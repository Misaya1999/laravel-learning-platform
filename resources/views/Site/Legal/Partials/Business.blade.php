<div class="legal-contact-card">
    <h2>Thông tin đơn vị vận hành</h2>
    <dl>
        <div><dt>Tên website</dt><dd>{{ config('business.name') }}</dd></div>
        @if(config('business.owner'))<div><dt>Đơn vị hoặc cá nhân vận hành</dt><dd>{{ config('business.owner') }}</dd></div>@endif
        @if(config('business.address'))<div><dt>Địa chỉ</dt><dd>{{ config('business.address') }}</dd></div>@endif
        @if(config('business.phone'))<div><dt>Điện thoại</dt><dd>{{ config('business.phone') }}</dd></div>@endif
        <div><dt>Email hỗ trợ</dt><dd><a href="mailto:{{ config('business.email') }}">{{ config('business.email') }}</a></dd></div>
        @if(config('business.tax_code'))<div><dt>Mã số thuế</dt><dd>{{ config('business.tax_code') }}</dd></div>@endif
    </dl>
    @if(!config('business.owner') || !config('business.address'))<p class="legal-pending-note">Thông tin đơn vị vận hành sẽ được cập nhật đầy đủ trước khi KhoaHocPlus chính thức cung cấp khóa học trả phí.</p>@endif
</div>
