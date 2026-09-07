@extends('Site.Layout.Index')
@section('title', 'Chính sách bảo mật — KhoaHocPlus')
@section('content')
<section class="legal-hero"><div class="site-container"><span class="eyebrow">Dữ liệu cá nhân</span><h1>Chính sách bảo mật</h1><p>Cập nhật lần cuối: 19/08/2026</p></div></section>
<section class="legal-section"><div class="site-container legal-layout"><article class="legal-content">
    <p>Chính sách này mô tả cách KhoaHocPlus thu thập, sử dụng, lưu trữ và bảo vệ dữ liệu cá nhân của người dùng.</p>
    <h2>1. Dữ liệu được thu thập</h2><p>Họ tên, email, số điện thoại, avatar; thông tin tài khoản; đơn hàng và khóa học; tiến độ học, đánh giá; nội dung hỗ trợ; địa chỉ IP, thiết bị, trình duyệt và nhật ký bảo mật cần thiết.</p>
    <h2>2. Mục đích xử lý</h2><p>Dữ liệu được dùng để tạo và bảo vệ tài khoản, cung cấp khóa học, xử lý đơn hàng, gửi thông báo giao dịch và hết hạn, hỗ trợ người dùng, ngăn chặn gian lận và cải thiện dịch vụ.</p>
    <h2>3. Chia sẻ dữ liệu</h2><p>KhoaHocPlus không bán dữ liệu cá nhân. Dữ liệu chỉ được chia sẻ ở phạm vi cần thiết với nhà cung cấp hosting, email, thanh toán, bảo mật hoặc cơ quan có thẩm quyền khi có yêu cầu hợp pháp.</p>
    <h2>4. Thời gian lưu trữ</h2><p>Dữ liệu tài khoản được lưu khi tài khoản hoạt động. Dữ liệu đơn hàng được lưu trong thời gian cần thiết để đối soát, giải quyết khiếu nại và đáp ứng nghĩa vụ pháp luật. Dữ liệu không còn cần thiết sẽ được xóa hoặc ẩn danh.</p>
    <h2>5. Quyền của người dùng</h2><p>Người dùng có thể yêu cầu biết, xem, cập nhật, sửa, hạn chế hoặc xóa dữ liệu trong phạm vi pháp luật cho phép; có quyền rút lại sự đồng ý phù hợp và khiếu nại về việc xử lý dữ liệu.</p>
    <h2>6. Bảo mật</h2><p>Mật khẩu được lưu dưới dạng mã hóa một chiều. KhoaHocPlus áp dụng phân quyền truy cập, kiểm soát phiên đăng nhập, sao lưu và các biện pháp kỹ thuật phù hợp để hạn chế truy cập trái phép.</p>
    <h2>7. Cookie</h2><p>Cookie được sử dụng để duy trì đăng nhập, bảo vệ phiên làm việc, lưu giỏ hàng và hỗ trợ vận hành website. Nếu sử dụng cookie phân tích hoặc quảng cáo trong tương lai, website sẽ cung cấp thông báo phù hợp.</p>
    <h2>8. Dữ liệu thanh toán</h2><p>KhoaHocPlus không lưu trực tiếp số thẻ hoặc mật khẩu ngân hàng. Khi tích hợp cổng thanh toán, dữ liệu nhạy cảm sẽ do đơn vị thanh toán xử lý theo chính sách của họ.</p>
    <h2>9. Liên hệ</h2><p>Người dùng có thể gửi yêu cầu liên quan đến dữ liệu cá nhân qua email hỗ trợ. KhoaHocPlus sẽ xác minh danh tính trước khi xử lý yêu cầu nhạy cảm.</p>
</article><aside>@include('Site.Legal.Partials.Business')</aside></div></section>
@endsection
