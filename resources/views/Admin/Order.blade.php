@extends('Admin.Layout.Index')

@section('content')
<style>
    .container-fluid { padding: 20px 20px 0 !important; }.order-toolbar { display: flex; gap: 10px; flex-wrap: wrap; }.order-toolbar input { min-width: 260px; flex: 1; }.order-code { font-weight: 700; }.order-course-list { margin: 0; padding-left: 17px; }.order-detail-row { padding: 10px 0; display: flex; justify-content: space-between; gap: 20px; border-bottom: 1px solid #eee; }
</style>
<div class="page-breadcrumb"><div class="row"><div class="col-6"><h4 class="page-title">Quản lý đơn hàng</h4></div><div class="col-6 text-right"><a href="{{ route('Admin.Dashboard') }}">Dashboard</a> / Đơn hàng</div></div></div>
<div class="container-fluid">
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card">
        <div class="card-body"><form method="GET" class="order-toolbar">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm mã đơn, user hoặc khóa học...">
            <select name="status" class="form-control" style="width:auto"><option value="">Tất cả trạng thái</option><option value="pending" @selected(request('status')==='pending')>Chờ thanh toán</option><option value="paid" @selected(request('status')==='paid')>Đã thanh toán</option><option value="cancelled" @selected(request('status')==='cancelled')>Đã hủy</option><option value="refunded" @selected(request('status')==='refunded')>Đã hoàn tiền</option></select>
            <select name="sort" class="form-control" style="width:auto"><option value="latest">Mới nhất</option><option value="oldest" @selected(request('sort')==='oldest')>Cũ nhất</option><option value="total_desc" @selected(request('sort')==='total_desc')>Giá trị cao nhất</option><option value="total_asc" @selected(request('sort')==='total_asc')>Giá trị thấp nhất</option></select>
            <button class="btn btn-primary">Tìm kiếm</button>@if(request()->hasAny(['search','status','sort']))<a href="{{ route('Admin.Order') }}" class="btn btn-outline-secondary">Reset</a>@endif
        </form></div>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Khóa học</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày tạo</th><th>Thao tác</th></tr></thead><tbody>
        @forelse ($orders as $order)
            <tr>
                <td><span class="order-code">{{ $order->code }}</span></td>
                <td><strong>{{ $order->user?->name ?? 'Đã xóa' }}</strong><br><small class="text-muted">{{ $order->user?->email }}</small></td>
                <td><ul class="order-course-list">@foreach($order->items as $item)<li>{{ $item->course_name }}</li>@endforeach</ul></td>
                <td><strong>{{ number_format($order->total,0,',','.') }} ₫</strong></td>
                <td>@php($statusMap=['pending'=>['badge-warning','Chờ thanh toán'],'paid'=>['badge-success','Đã thanh toán'],'cancelled'=>['badge-secondary','Đã hủy'],'refunded'=>['badge-danger','Đã hoàn tiền']])<span class="badge {{ $statusMap[$order->status][0] }}">{{ $statusMap[$order->status][1] }}</span>@if($order->status==='pending' && $order->receipt_path)<br><span class="badge badge-info mt-1">Có biên lai</span>@endif</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-nowrap"><button class="btn btn-info btn-sm" data-toggle="modal" data-target="#orderModal{{ $order->id }}">Chi tiết</button>
                    @if($order->status==='pending')<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#payOrderModal{{ $order->id }}">Xác nhận paid</button><form action="{{ route('Admin.Order.Status',$order) }}" method="POST" class="d-inline" onsubmit="return confirm('Hủy đơn hàng này?')">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button class="btn btn-secondary btn-sm">Hủy</button></form>
                    @elseif($order->status==='paid')<form action="{{ route('Admin.Order.Status',$order) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận hoàn tiền và thu hồi quyền học?')">@csrf @method('PATCH')<input type="hidden" name="status" value="refunded"><button class="btn btn-danger btn-sm">Hoàn tiền</button></form>@endif
                </td>
            </tr>
        @empty <tr><td colspan="7" class="text-center text-muted py-4">Chưa có đơn hàng.</td></tr>@endforelse
        </tbody></table></div>
        <div class="card-body">{{ $orders->links() }}</div>
    </div>
</div>

@foreach($orders as $order)
<div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5>Đơn hàng {{ $order->code }}</h5><button class="close" data-dismiss="modal">×</button></div><div class="modal-body">
    <div class="order-detail-row"><span>Khách hàng</span><strong>{{ $order->user?->name }} · {{ $order->user?->email }}</strong></div>
    <div class="order-detail-row"><span>Phương thức</span><strong>{{ match($order->payment_method) { 'payos' => 'VietQR tự động qua payOS', 'bank_transfer' => 'Chuyển khoản thủ công', 'free' => 'Đăng ký miễn phí', default => $order->payment_method } }}</strong></div>
    <div class="order-detail-row"><span>Mã giao dịch</span><strong>{{ $order->transaction_id ?: '—' }}</strong></div>
    <div class="order-detail-row"><span>Thanh toán lúc</span><strong>{{ $order->paid_at?->format('d/m/Y H:i') ?? '—' }}</strong></div>
    <div class="order-detail-row"><span>Người xác nhận</span><strong>{{ $order->confirmedBy?->name ?? '—' }}</strong></div>
    <div class="order-detail-row"><span>Biên lai</span><strong>@if($order->receipt_path)<a href="{{ route('Admin.Order.Receipt',$order) }}" target="_blank" class="btn btn-info btn-sm">Xem ảnh · {{ $order->receipt_submitted_at?->format('d/m/Y H:i') }}</a>@else Chưa gửi @endif</strong></div>
    @foreach($order->items as $item)<div class="order-detail-row"><span>{{ $item->course_name }}</span><strong>{{ number_format($item->price,0,',','.') }} ₫</strong></div>@endforeach
    <div class="order-detail-row"><strong>Tổng cộng</strong><strong>{{ number_format($order->total,0,',','.') }} ₫</strong></div>
</div><div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Đóng</button></div></div></div></div>
@if($order->status==='pending')<div class="modal fade" id="payOrderModal{{ $order->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('Admin.Order.Status',$order) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="paid"><div class="modal-header"><h5>Xác nhận thanh toán</h5><button type="button" class="close" data-dismiss="modal">×</button></div><div class="modal-body"><p>Xác nhận đã nhận <strong>{{ number_format($order->total,0,',','.') }} ₫</strong> cho đơn {{ $order->code }}.</p><label>Mã giao dịch (không bắt buộc)</label><input name="transaction_id" class="form-control" maxlength="150"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button><button class="btn btn-success">Xác nhận paid</button></div></form></div></div></div>@endif
@endforeach
@endsection
