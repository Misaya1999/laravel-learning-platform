<!DOCTYPE html>
<html dir="ltr" lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('Admin/assets/images/favicon.png') }}">
    <title>Quản trị — KhoaHocPlus</title>
    <link href="{{ asset('Admin/assets/libs/chartist/dist/chartist.min.css') }}" rel="stylesheet">
    <link href="{{ asset('Admin/dist/css/style.min.css') }}" rel="stylesheet">
    <style>
        body,
        h1, h2, h3, h4, h5, h6,
        p, a, span, strong, small,
        table, th, td,
        button, input, select, textarea, label,
        .form-control, .btn, .dropdown-menu, .modal {
            font-family: "Segoe UI", Arial, sans-serif !important;
        }
        .topbar .top-navbar .navbar-header,
        .topbar .top-navbar .navbar-header .navbar-brand {
            height: 64px;
            background: #233242;
        }
        .topbar .top-navbar .navbar-header .navbar-brand {
            width: 100%;
            padding: 0 12px;
        }
        .admin-brand-logo {
            width: 205px;
            height: 64px;
            display: flex !important;
            align-items: center;
            overflow: visible;
        }
        .admin-brand-logo img { display: block; width: 205px; height: auto; max-width: none; }
        .admin-header-user { gap: 8px; }
        .admin-header-avatar {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            background: #2962ff;
            font-size: 13px;
            font-weight: 700;
        }
        .admin-header-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .admin-header-name { max-width: 160px; overflow: hidden; color: #3e5569; font-size: 13px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
        .admin-dropdown-summary { padding: 12px 20px 8px; display: grid; gap: 3px; }
        .admin-dropdown-summary strong { color: #3e5569; font-size: 14px; }
        .admin-dropdown-summary small { overflow: hidden; color: #8a929a; font-size: 11px; text-overflow: ellipsis; }
        .admin-logout-form { margin: 0; }
        .admin-logout-form .dropdown-item { width: 100%; border: 0; background: transparent; text-align: left; cursor: pointer; }
        .admin-order-alert { min-width: 20px; height: 20px; margin-left: auto; padding: 0 6px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; color: #fff; background: #fa5838; font-size: 10px; font-weight: 800; opacity: 1; }
        .admin-order-alert[hidden] { display: none !important; }
        .admin-receipt-alert { min-width: 20px; height: 20px; margin-left: 4px; padding: 0 6px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; color: #fff; background: #137eff; font-size: 10px; font-weight: 800; opacity: 1; animation: receipt-pulse 1.8s infinite; }.admin-receipt-alert[hidden] { display:none !important; }@keyframes receipt-pulse { 50% { box-shadow:0 0 0 5px rgba(19,126,255,0); } 0%,100% { box-shadow:0 0 0 0 rgba(19,126,255,.35); } }
        @media (min-width: 768px) {
            #main-wrapper .left-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                height: 100vh;
            }
            #main-wrapper .left-sidebar .scroll-sidebar {
                height: calc(100vh - 64px);
                overflow-x: hidden;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: rgba(255, 255, 255, .25) transparent;
            }
        }
    </style>
</head>
<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <div id="main-wrapper" data-navbarbg="skin6" data-theme="light" data-layout="vertical" data-sidebartype="full" data-boxed-layout="full">
      
        @include('Admin.Layout.Header')

        @include('Admin.Layout.LeftBar')

        <div class="page-wrapper">
            @yield('content')
        </div>
    </div>
    
    <script src="{{ asset('Admin/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('Admin/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('Admin/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('Admin/assets/extra-libs/sparkline/sparkline.js') }}"></script>
    <script src="{{ asset('Admin/dist/js/waves.js') }}"></script>
    <script src="{{ asset('Admin/dist/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('Admin/dist/js/custom.min.js') }}"></script>
    <script src="{{ asset('Admin/assets/libs/chartist/dist/chartist.min.js') }}"></script>
    <script src="{{ asset('Admin/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js') }}"></script>
    <script>
    (() => {
        const endpoint = @json(route('Admin.LiveStatus'));
        const refreshAdminStatus = async () => {
            try {
                const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                if (!response.ok) return;
                const data = await response.json();
                const orderBadge = document.querySelector('[data-pending-order-count]');
                if (orderBadge) {
                    orderBadge.textContent = data.pending_orders;
                    orderBadge.hidden = data.pending_orders < 1;
                }
                const receiptBadge = document.querySelector('[data-submitted-receipt-count]');
                if (receiptBadge) {
                    receiptBadge.textContent = data.submitted_receipts;
                    receiptBadge.hidden = data.submitted_receipts < 1;
                }
                document.querySelectorAll('[data-online-count]').forEach(el => el.textContent = data.online);
                document.querySelectorAll('[data-offline-count]').forEach(el => el.textContent = data.offline);
                const onlineIds = new Set(data.online_user_ids.map(String));
                document.querySelectorAll('[data-user-id]').forEach(row => {
                    const status = row.querySelector('[data-presence-status]');
                    if (!status) return;
                    const isOnline = onlineIds.has(row.dataset.userId);
                    status.classList.toggle('is-online', isOnline);
                    status.textContent = isOnline ? 'Online' : 'Offline';
                });
            } catch (_) {}
        };
        refreshAdminStatus();
        window.setInterval(refreshAdminStatus, 15000);
    })();
    </script>
    @stack('scripts')
</body>
