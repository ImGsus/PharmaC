<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ucfirst(AppSettings::get('app_name', 'App'))}} - {{ucfirst($title ?? '')}}</title>
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
    <!-- Complete icon glyph definitions -->
    <link rel="stylesheet" href="{{asset('assets/css/icons.min.css')}}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/animate.min.css')}}">
    <!-- Page CSS -->
    @stack('page-css')
    <script>
        (function () {
            var root = document.documentElement;
            try {
                var storageKey = 'pharmacy-theme';
                var savedTheme = localStorage.getItem(storageKey);
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var isDark = savedTheme === 'dark' || (savedTheme !== 'light' && prefersDark);

                root.classList.toggle('dark-mode', isDark);
                root.style.colorScheme = isDark ? 'dark' : 'light';
                root.setAttribute('data-theme', isDark ? 'dark' : 'light');

                if (document.body) {
                    document.body.classList.toggle('dark-mode', isDark);
                    document.body.setAttribute('data-theme', isDark ? 'dark' : 'light');
                }
            } catch (error) {
                root.style.colorScheme = 'light';
            }
        })();
    </script>
    <style>
        html {
            background: #f5f7fb;
            visibility: visible;
        }
        html.dark-mode {
            background: #0f1418;
        }
        html.theme-initializing,
        html.theme-initializing * {
            transition: none !important;
            animation: none !important;
        }
        body {
            opacity: 1;
            transition: opacity 180ms ease, background-color 180ms ease;
        }
        body.page-transitioning {
            opacity: 0;
        }
        html.dark-mode .login-body,
        body.dark-mode .login-body {
            background-color: #0f1418 !important;
            background-image: linear-gradient(rgba(148, 163, 184, .055) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, .055) 1px, transparent 1px) !important;
        }
        .login-body,
        .login-wrapper .loginbox,
        .login-wrapper .loginbox .login-right,
        .login-wrapper .loginbox h1,
        .login-wrapper .loginbox .account-subtitle,
        .login-wrapper .loginbox .dont-have,
        .login-wrapper .loginbox .dont-have a,
        .login-wrapper .loginbox .forgotpass a,
        .login-wrapper .loginbox .form-control,
        .login-wrapper .loginbox .btn-primary {
            transition: background-color .35s ease, border-color .35s ease, box-shadow .35s ease, color .35s ease;
        }
        .login-body {
            background-color: #f8fafc;
            background-image: linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px), linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
            background-size: 80px 80px;
        }
        .login-actions {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 3;
        }
        .night-mode-toggle {
            display: inline-flex;
            width: 2.75rem;
            height: 2.75rem;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: #f59e0b;
            cursor: pointer;
            transition: color .35s ease, transform .2s ease;
        }
        .night-mode-toggle:hover { transform: scale(1.08); }
        .theme-switch-icon {
            width: 1.75rem;
            height: 1.75rem;
            overflow: visible;
            transform: rotate(90deg);
            transition: transform .55s cubic-bezier(.4, 1.6, .6, 1);
        }
        .theme-switch-sun { fill: currentColor; transition: r .55s cubic-bezier(.4, 1.6, .6, 1); }
        .theme-switch-mask { transition: cx .55s cubic-bezier(.4, 1.6, .6, 1), cy .55s cubic-bezier(.4, 1.6, .6, 1); }
        .theme-switch-rays { fill: none; stroke-width: 2; stroke-linecap: round; transition: opacity .35s ease; }
        .night-mode-toggle.is-dark { color: #f8fafc; }
        .night-mode-toggle.is-dark .theme-switch-icon { transform: rotate(40deg); }
        .night-mode-toggle.is-dark .theme-switch-sun { r: 9; }
        .night-mode-toggle.is-dark .theme-switch-mask { cx: 12px; cy: 5.52px; }
        .night-mode-toggle.is-dark .theme-switch-rays { opacity: 0; }
        .login-wrapper .loginbox .login-left {
            background: linear-gradient(rgba(11, 63, 93, .75), rgba(0, 208, 241, .6)), url('{{ asset('assets/img/img-01.jpg') }}') center/cover no-repeat;
        }
        body.dark-mode .login-body {
            background-color: #0f1418 !important;
            background-image: linear-gradient(rgba(148, 163, 184, .055) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, .055) 1px, transparent 1px) !important;
            background-size: 80px 80px;
        }
        body.dark-mode .login-wrapper .loginbox {
            background-color: #1c2025;
            border-color: rgba(255, 255, 255, .10);
            box-shadow: 0 20px 50px rgba(0, 0, 0, .55);
        }
        body.dark-mode .login-wrapper .loginbox .login-left {
            background: linear-gradient(rgba(8, 74, 58, .82), rgba(20, 151, 105, .72)), url('{{ asset('assets/img/img-01.jpg') }}') center/cover no-repeat;
        }
        body.dark-mode .login-wrapper .loginbox .login-right {
            background-color: #1c2025;
        }
        body.dark-mode .login-wrapper .loginbox h1,
        body.dark-mode .login-wrapper .loginbox .account-subtitle,
        body.dark-mode .login-wrapper .loginbox .dont-have,
        body.dark-mode .login-wrapper .loginbox .dont-have a,
        body.dark-mode .login-wrapper .loginbox .forgotpass a {
            color: #e7e9ec;
        }
        body.dark-mode .login-wrapper .loginbox .form-control {
            background-color: #111827;
            border-color: #334155;
            color: #f8fafc;
        }
        body.dark-mode .login-wrapper .loginbox .form-control::placeholder {
            color: #94a3b8;
        }
        body.dark-mode .login-wrapper .loginbox .form-control:focus:not(.is-invalid) {
            border-color: #69d9aa !important;
            box-shadow: 0 0 0 2px rgba(105, 217, 170, 0.18) !important;
            outline: none;
        }
        body:not(.dark-mode) .login-wrapper .loginbox .form-control:focus:not(.is-invalid) {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.18) !important;
            outline: none;
        }

        body:not(.dark-mode) .login-wrapper .loginbox .dont-have,
        body:not(.dark-mode) .login-wrapper .loginbox .forgotpass {
            color: #111827;
        }
        body:not(.dark-mode) .login-wrapper .loginbox .dont-have a,
        body:not(.dark-mode) .login-wrapper .loginbox .forgotpass a {
            color: #2563eb !important;
        }
        body:not(.dark-mode) .login-wrapper .loginbox .dont-have a:hover,
        body:not(.dark-mode) .login-wrapper .loginbox .forgotpass a:hover {
            color: #1d4ed8 !important;
        }

        body.dark-mode .login-wrapper .loginbox .dont-have,
        body.dark-mode .login-wrapper .loginbox .forgotpass {
            color: #f8fafc;
        }
        body.dark-mode .login-wrapper .loginbox .dont-have a,
        body.dark-mode .login-wrapper .loginbox .forgotpass a {
            color: #69d9aa !important;
        }
        body.dark-mode .login-wrapper .loginbox .dont-have a:hover,
        body.dark-mode .login-wrapper .loginbox .forgotpass a:hover {
            color: #8be6c0 !important;
        }
        /* Keep Chrome/Edge autofill + suggestion highlight dark so text stays readable. */
        body.dark-mode .login-wrapper .loginbox .form-control:-webkit-autofill,
        html.dark-mode .login-wrapper .loginbox .form-control:-webkit-autofill,
        body.dark-mode .login-wrapper .loginbox .form-control:-webkit-autofill:hover,
        html.dark-mode .login-wrapper .loginbox .form-control:-webkit-autofill:hover,
        body.dark-mode .login-wrapper .loginbox .form-control:-webkit-autofill:focus,
        html.dark-mode .login-wrapper .loginbox .form-control:-webkit-autofill:focus,
        body.dark-mode .login-wrapper .loginbox .form-control:-webkit-autofill:active,
        html.dark-mode .login-wrapper .loginbox .form-control:-webkit-autofill:active,
        body.dark-mode .login-wrapper .loginbox .form-control:autofill,
        html.dark-mode .login-wrapper .loginbox .form-control:autofill {
            -webkit-box-shadow: 0 0 0 3rem #111827 inset !important;
            box-shadow: 0 0 0 3rem #111827 inset !important;
            -webkit-text-fill-color: #f8fafc !important;
            caret-color: #f8fafc !important;
            border-color: #334155 !important;
        }
        body.dark-mode .login-wrapper .loginbox .btn-primary {
            background-color: #3db985;
            border-color: #3db985;
        }
        body.dark-mode .login-wrapper .loginbox .btn-primary:hover {
            background-color: #69d9aa;
            border-color: #69d9aa;
        }
        .login-wrapper .loginbox {
            position: relative;
        }
        .login-validation-alerts {
            position: absolute;
            left: calc(100% + 20px);
            top: 20px;
            width: 240px;
            z-index: 2;
        }
        .login-validation-alerts .alert {
            margin-bottom: 0.5rem;
            width: 100%;
        }
        @media (max-width: 767px) {
            .login-actions {
                top: .75rem;
                right: .75rem;
            }
            .login-validation-alerts {
                position: static;
                width: 100%;
                padding: 1rem 1rem 0;
            }
        }
    </style>
    <!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js"></script>
        <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body data-auth-page="{{ request()->route() ? (request()->route()->getName() ?: request()->path()) : request()->path() }}">
    <script>
        if (document.documentElement.classList.contains('dark-mode')) {
            document.body.classList.add('dark-mode');
        }
    </script>

    <div class="login-actions text-center">
        <button type="button" id="loginThemeToggle" class="night-mode-toggle" role="switch" aria-checked="false" aria-label="Switch to dark mode">
            <svg class="theme-switch-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <defs>
                    <mask id="loginThemeMoonMask">
                        <rect width="24" height="24" fill="white" />
                        <circle class="theme-switch-mask" cx="24" cy="0" r="9" fill="black" />
                    </mask>
                </defs>
                <circle class="theme-switch-sun" cx="12" cy="12" r="5" mask="url(#loginThemeMoonMask)" />
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
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper login-body">
        <div class="login-wrapper">
            <div class="container">
                <div class="loginbox">
                    <div class="login-left animate__animated animate__fadeInLeft">
                        @php
                            $plainLogoPath = AppSettings::get('logo');
                            $plainLogoUrl = !empty($plainLogoPath)
                                ? asset('storage/' . $plainLogoPath) . (file_exists(public_path('storage/' . $plainLogoPath)) ? '?v=' . filemtime(public_path('storage/' . $plainLogoPath)) : '')
                                : asset('assets/img/logo-white.png');
                        @endphp
                            <img id="plain-site-logo" class="img-fluid" src="{{ $plainLogoUrl }}" alt="Logo"
                                data-light-logo="{{ asset('assets/img/logo-white.png') }}"
                                data-dark-logo="{{ asset('assets/img/logo.png') }}">
                    </div>
                    <div class="login-right animate__animated animate__fadeInRight">
                        <div class="login-right-wrap">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Main Wrapper -->
    
</body>
<!-- jQuery -->
<script src="{{asset('assets/js/jquery-3.2.1.min.js')}}"></script>

<!-- Bootstrap Core JS -->
<script src="{{asset('assets/js/popper.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap.min.js')}}"></script>

<!-- Custom JS -->
<script src="{{asset('assets/js/script.js')}}"></script>
<script src="{{ asset('assets/js/auth-validation.js') }}"></script>
<!-- Page JS -->
@stack('page-js')
<script>
    $(function() {
        var authAnimationKey = 'pharmacy-auth-animation-page';
        var currentAuthPage = $('body').data('auth-page');
        var previousAuthPage = sessionStorage.getItem(authAnimationKey);

        if (previousAuthPage === currentAuthPage) {
            $('.loginbox .animate__animated').removeClass('animate__animated');
        } else {
            sessionStorage.setItem(authAnimationKey, currentAuthPage);
        }
    });
</script>
</html>