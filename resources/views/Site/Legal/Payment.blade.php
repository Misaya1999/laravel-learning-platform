@extends('Site.Layout.Index')
@section('title', 'Chính sách thanh toán — KhoaHocPlus')
@section('content')
<section class="legal-hero"><div class="site-container"><span class="eyebrow">Đơn hàng và học phí</span><h1>Chính sách thanh toán</h1><p>Cập nhật lần cuối: 19/08/2026</p></div></section>
<section class="legal-section"><div class="site-container legal-layout"><article class="legal-content">
    <h2>1. Phương thức thanh toán</h2><p>KhoaHocPlus hiện tiếp nhận thanh toán bằng chuyển khoản ngân hàng. Khóa học miễn phí được kích hoạt ngay và không yêu cầu thanh toán. Các phương thức khác sẽ được công bố khi chính thức hỗ trợ.</p>
    <h2>2. Xác nhận đơn hàng</h2><p>Đơn trả phí ở trạng thái chờ cho đến khi admin đối soát và xác nhận. Người dùng cần chuyển đúng số tiền, nội dung và mã đơn. Sau khi xác nhận, khóa học được thêm vào tài khoản theo thời hạn đã công bố.</p>
    <h2>3. Chính sách không hoàn học phí</h2><p>Do khóa học là nội dung số được cấp quyền truy cập ngay sau khi thanh toán, học phí không được hoàn lại sau khi khóa học đã được kích hoạt nếu người dùng thay đổi nhu cầu, không sắp xếp được thời gian học hoặc không đạt kết quả kỳ vọng trong khi nội dung vẫn được cung cấp đúng mô tả.</p>
    <h2>4. Trường hợp cần đối soát và khắc phục</h2><p>Chính sách không hoàn học phí không loại trừ quyền của người tiêu dùng theo pháp luật. KhoaHocPlus sẽ kiểm tra và xử lý khi thanh toán bị trùng, đã nhận tiền nhưng không cấp được quyền truy cập, khóa học không thể cung cấp, hoặc nội dung sai khác đáng kể so với mô tả tại thời điểm mua.</p>
    <p>Tùy từng trường hợp, biện pháp xử lý có thể là sửa lỗi, cấp lại hoặc gia hạn quyền truy cập, chuyển sang khóa học tương đương; nếu không thể khắc phục thì thực hiện hoàn lại khoản tiền phù hợp theo quy định pháp luật.</p>
    <h2>5. Hủy đơn chưa thanh toán</h2><p>Người dùng có thể tự hủy đơn đang chờ nếu chưa chuyển khoản. Việc hủy đơn không phát sinh học phí và không cấp quyền truy cập khóa học.</p>
    <h2>6. Yêu cầu hỗ trợ</h2><p>Khi cần đối soát, người dùng gửi họ tên, email tài khoản, mã đơn, khóa học và chứng từ thanh toán tới email hỗ trợ. KhoaHocPlus dự kiến phản hồi trong vòng 3–7 ngày làm việc.</p>
</article><aside>@include('Site.Legal.Partials.Business')</aside></div></section>
@endsection
