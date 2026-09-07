<p align="center">
  <img src="public/Site/images/logo-transparent.png" alt="KhoaHocPlus" width="180">
</p>

<h1 align="center">KhoaHocPlus</h1>

<p align="center">
  Nền tảng bán và quản lý khóa học trực tuyến được xây dựng bằng Laravel.
</p>

## Giới thiệu

KhoaHocPlus là ứng dụng web học trực tuyến, bao phủ luồng từ khám phá khóa học, đặt hàng và thanh toán đến theo dõi tiến độ học. Dự án đồng thời cung cấp khu vực quản trị để vận hành nội dung, người dùng, giảng viên, đơn hàng và truyền thông trên hệ thống.

## Chức năng nổi bật

### Học viên

- Đăng ký, đăng nhập, xác minh email, khôi phục mật khẩu và đăng nhập Google.
- Tìm hiểu khóa học, giảng viên và bài viết trước khi mua.
- Thanh toán qua PayOS hoặc chuyển khoản thủ công kèm biên lai.
- Theo dõi đơn hàng và trạng thái thanh toán.
- Xem video, tài liệu PDF, tải tài liệu được cấp quyền và lưu tiến độ bài học.
- Đánh giá khóa học, bình luận/chấm điểm bài viết và nhận thông báo.
- Quản lý hồ sơ và danh sách khóa học đã mua.

### Quản trị viên

- Dashboard thống kê hoạt động học tập và trạng thái người dùng trực tuyến.
- Quản lý danh mục, khóa học, chương, bài học và upload video theo từng phần.
- Quản lý giảng viên, chứng chỉ, học viên và quyền truy cập.
- Duyệt đơn hàng, biên lai, đánh giá và trạng thái thanh toán.
- Soạn bài blog bằng trình soạn thảo nội dung và quản lý bình luận/đánh giá.
- Tạo thông báo và gửi email cho các sự kiện quan trọng.
- Tác vụ nền tự động hủy đơn quá hạn và nhắc khóa học sắp hết hạn.

## Công nghệ

- PHP 8.2, Laravel 12, Eloquent ORM và Blade
- SQLite cho môi trường phát triển; có thể cấu hình MySQL/PostgreSQL qua biến môi trường
- Bootstrap 5, Sass, Tailwind CSS 4 và Vite 7
- Laravel Socialite cho Google OAuth
- CKEditor 5 cho nội dung blog và PDF.js cho tài liệu học
- PHPUnit cho kiểm thử tự động

## Cài đặt cục bộ

Yêu cầu: PHP 8.2+, Composer, Node.js và npm.

```bash
git clone <repository-url>
cd main-project
composer setup
php artisan serve
```

Sau khi chạy `composer setup`, ứng dụng mặc định sử dụng SQLite. Mở `http://localhost:8000` để sử dụng.

Nếu cần tài khoản quản trị cục bộ, thêm các biến sau vào `.env`, sau đó chạy seeder:

```dotenv
ADMIN_NAME="Local Admin"
ADMIN_EMAIL="admin@example.com"
ADMIN_PASSWORD="change-this-password"
ADMIN_PHONE=
```

```bash
php artisan db:seed --class=AdminUserSeeder
```

Không sử dụng thông tin đăng nhập mẫu trên môi trường production.

## Cấu hình tích hợp

Các dịch vụ ngoài đều được cấu hình qua `.env` và không lưu khóa bí mật trong Git:

- PayOS: `PAYOS_CLIENT_ID`, `PAYOS_API_KEY`, `PAYOS_CHECKSUM_KEY`
- Google OAuth: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
- Email: nhóm biến `MAIL_*`
- Lưu trữ S3-compatible: nhóm biến `AWS_*`

Xem toàn bộ biến cấu hình trong [`.env.example`](.env.example).

## Kiểm thử và build

```bash
composer test
npm run build
```

Test suite bao phủ các luồng quan trọng như xác thực, phân quyền học, checkout/đơn hàng, đánh giá khóa học, blog, thông báo, hồ sơ giảng viên và thống kê.

## Cấu trúc chính

```text
app/Http/Controllers/Admin  Khu vực quản trị
app/Http/Controllers/Site   Luồng dành cho học viên
app/Models                  Mô hình dữ liệu Eloquent
app/Services                Thanh toán và nghiệp vụ đơn hàng
database/migrations         Lược đồ cơ sở dữ liệu
resources/views             Giao diện Blade
routes/web.php              Định tuyến web và middleware
tests/Feature               Kiểm thử hành vi ứng dụng
```

## Bảo mật

- `.env`, database cục bộ, file build và dependencies không được đưa vào Git.
- File bài học được phục vụ qua kiểm tra quyền; đường dẫn tải tài liệu sử dụng signed URL và rate limit.
- Nội dung blog được làm sạch trước khi hiển thị.
- Webhook thanh toán được xử lý phía server.

Nếu phát hiện lỗ hổng, vui lòng báo riêng cho chủ dự án thay vì tạo issue công khai.

## Tác giả

Dự án portfolio cá nhân. Thông tin tác giả, đường dẫn demo và ảnh chụp sản phẩm sẽ được bổ sung trước khi công khai repository.
