<aside class="left-sidebar" data-sidebarbg="skin5">
    <div class="scroll-sidebar">
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{route('Admin.Dashboard')}}" aria-expanded="false">
                        <i class="mdi mdi-av-timer"></i>
                        <span class="hide-menu">Tổng quan</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{route('Admin.Profile')}}" aria-expanded="false">
                        <i class="mdi mdi-face-profile"></i>
                        <span class="hide-menu">Hồ sơ</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{route('Admin.Category')}}" aria-expanded="false">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <span class="hide-menu">Danh mục</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{route('Admin.Course')}}" aria-expanded="false">
                        <i class="mdi mdi-library-books"></i>
                        <span class="hide-menu">Khóa học</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('Admin.Blog') }}" aria-expanded="false">
                        <i class="mdi mdi-lead-pencil"></i>
                        <span class="hide-menu">Blog</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('Admin.Mentor') }}" aria-expanded="false">
                        <i class="mdi mdi-account-edit"></i>
                        <span class="hide-menu">Người hướng dẫn</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{route('Admin.User')}}" aria-expanded="false">
                        <i class="mdi mdi-human-male-female"></i>
                        <span class="hide-menu">Người dùng</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('Admin.Order') }}" aria-expanded="false">
                        <i class="mdi mdi-cart-outline"></i>
                        <span class="hide-menu">Đơn hàng</span>
                        <span class="admin-order-alert" data-pending-order-count hidden>0</span>
                        <span class="admin-receipt-alert" data-submitted-receipt-count hidden title="Biên lai chờ kiểm tra">0</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('Admin.Review') }}" aria-expanded="false">
                        <i class="mdi mdi-star-outline"></i>
                        <span class="hide-menu">Đánh giá</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ route('Admin.Announcement') }}" aria-expanded="false">
                        <i class="mdi mdi-bell-outline"></i>
                        <span class="hide-menu">Thông báo</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
