<header class="topbar" data-navbarbg="skin6">
    <nav class="navbar top-navbar navbar-expand-md navbar-light">
        <div class="navbar-header" data-logobg="skin5">
            <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)">
                <i class="ti-menu ti-close"></i>
            </a>
            <div class="navbar-brand">
                <a href="{{ route('Admin.Dashboard') }}" class="logo admin-brand-logo" aria-label="KhoaHocPlus Dashboard">
                    <img src="{{ asset('Site/images/logo-admin-light.png') }}" alt="KhoaHocPlus">
                </a>
            </div>
            <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="ti-more"></i>
            </a>
        </div>
        <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin6">
            <ul class="navbar-nav float-left mr-auto">
                <!-- <li class="nav-item search-box">
                    <a class="nav-link waves-effect waves-dark" href="javascript:void(0)">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-magnify font-20 mr-1"></i>
                            <div class="ml-1 d-none d-sm-block">
                                <span>Search</span>
                            </div>
                        </div>
                    </a>
                    <form class="app-search position-absolute">
                        <input type="text" class="form-control" placeholder="Search &amp; enter">
                        <a class="srh-btn">
                            <i class="ti-close"></i>
                        </a>
                    </form>
                </li> -->
            </ul>
            <ul class="navbar-nav float-right">
                <li class="nav-item">
                    <a class="nav-link waves-effect waves-dark d-flex align-items-center" href="{{ route('site.home') }}" target="_blank" rel="noopener noreferrer">
                        <i class="mdi mdi-web font-20 mr-1"></i>
                        <span class="d-none d-sm-inline">Xem website</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark admin-header-user" href="javascript:void(0)" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="admin-header-avatar">
                            @if (auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar của {{ auth()->user()->name }}">
                            @else
                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </span>
                        <span class="admin-header-name d-none d-sm-inline">{{ Illuminate\Support\Str::limit(auth()->user()->name, 20, '...') }}</span>
                        <i class="mdi mdi-chevron-down ml-1"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right user-dd animated">
                        <div class="admin-dropdown-summary">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ auth()->user()->email }}</small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('Admin.Profile') }}"><i class="ti-user m-r-5"></i> Hồ sơ admin</a>
                        <form action="{{ route('logout') }}" method="POST" class="admin-logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="ti-power-off m-r-5"></i> Đăng xuất</button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>
