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

        document.addEventListener('turbo:before-render', function (event) {
            window.dashboardChartRequestId = (window.dashboardChartRequestId || 0) + 1;
            $('.modal').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
            $('html').removeClass('menu-opened');
            $('.sidebar-overlay').removeClass('opened');
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

            nextBody.classList.toggle('dark-mode', isDark);
            nextBody.setAttribute('data-theme', isDark ? 'dark' : 'light');
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
            if (window.pharmacySidebarSlimScrollInit) {
                window.pharmacySidebarSlimScrollInit();
            }

            // Restore sidebar scroll position after slimscroll is re-initialized
            try {
                var savedSidebarScroll = sessionStorage.getItem('pharmacy-sidebar-scroll');
                if (savedSidebarScroll && parseInt(savedSidebarScroll, 10) > 0) {
                    var $sidebarEl = document.querySelector('.sidebar-inner.slimscroll');
                    if ($sidebarEl && window.jQuery) {
                        jQuery($sidebarEl).slimScroll({ scrollTo: savedSidebarScroll + 'px' });
                    }
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
        body.dark-mode input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
        body.dark-mode select,
        body.dark-mode textarea,
        body.dark-mode .form-control,
        body.dark-mode .dataTables_filter input,
        body.dark-mode .tabulator input,
        body.dark-mode .select2-container--default .select2-selection--single,
        body.dark-mode .select2-container--default .select2-selection--multiple {
            background-color: #111827 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.55) !important;
        }

        body.dark-mode input::placeholder,
        body.dark-mode textarea::placeholder,
        body.dark-mode .form-control::placeholder,
        body.dark-mode .dataTables_filter input::placeholder,
        body.dark-mode .tabulator input::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        body.dark-mode input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):focus,
        body.dark-mode select:focus,
        body.dark-mode textarea:focus,
        body.dark-mode .form-control:focus,
        body.dark-mode .dataTables_filter input:focus,
        body.dark-mode .tabulator input:focus,
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
                <!-- add sales modal-->
                <x-modals.add-sale />
                 <!-- / add sales modal -->
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
<!-- Select2 JS -->
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}" data-turbo-eval="false"></script>
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
    function initHeaderDateTime() {
        const el = document.getElementById('header-current-datetime');
        if (!el) {
            return;
        }

        if (window.pharmacyHeaderDateTimeInterval) {
            clearInterval(window.pharmacyHeaderDateTimeInterval);
        }

        function updateDateTime() {
            const now = new Date();
            const options = {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            el.textContent = now.toLocaleString('en-US', options);
        }

        updateDateTime();
        window.pharmacyHeaderDateTimeInterval = setInterval(updateDateTime, 1000);
    }

    /* Run on first load, on Turbo page navigations, and immediately (script sits at end of body). */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeaderDateTime);
    } else {
        initHeaderDateTime();
    }
    document.addEventListener('turbo:load', initHeaderDateTime);

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

    $(document).on('submit', 'form', function(e){
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
<!-- Page JS -->
@stack('page-js')
</body>
</html>