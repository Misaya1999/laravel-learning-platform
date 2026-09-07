@extends('Site.Layout.Index')

@section('title', 'Đội ngũ giảng viên — KhoaHocPlus')
@section('description', 'Gặp gỡ đội ngũ giảng viên và mentor đồng hành cùng bạn tại KhoaHocPlus.')

@section('content')
<section class="mentor-directory-hero">
    <div class="site-container mentor-directory-hero-inner">
        <div>
            <span class="eyebrow coral-text">Đội ngũ đồng hành</span>
            <h1>Những người dẫn đường<br><em>cho hành trình của bạn.</em></h1>
            <p>Gặp gỡ các giảng viên đang chia sẻ kiến thức, kinh nghiệm và góc nhìn thực tế tại KhoaHocPlus.</p>
        </div>
        <div class="mentor-directory-total"><strong>{{ $mentors->total() }}</strong><span>giảng viên</span></div>
    </div>
</section>

<section class="mentor-directory-section">
    <div class="site-container">
        <div class="mentor-directory-toolbar">
            <div><span class="eyebrow coral-text">Người hướng dẫn</span><h2>Đội ngũ giảng viên</h2></div>
            <form action="{{ route('site.mentor.index') }}" method="GET" class="mentor-directory-search">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Tìm tên hoặc chuyên môn..." aria-label="Tìm giảng viên">
                <button aria-label="Tìm kiếm">⌕</button>
            </form>
        </div>

        <div class="mentor-directory-grid">
            @forelse ($mentors as $mentor)
                <article class="mentor-directory-card {{ $mentor->is_main ? 'is-main-mentor' : '' }}">
                    <div class="mentor-directory-card-top">
                        <div class="mentor-directory-avatar">
                            @if ($mentor->avatar)
                                <img src="{{ asset('storage/' . $mentor->avatar) }}" alt="{{ $mentor->name }}">
                            @else
                                <span>{{ mb_strtoupper(mb_substr($mentor->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="mentor-directory-identity">
                            @if ($mentor->specialty)<span>{{ $mentor->specialty }}</span>@else<span>Giảng viên KhoaHocPlus</span>@endif
                            <h3>{{ $mentor->name }}</h3>
                            <small>{{ $mentor->courses_count }} khóa học đang giảng dạy</small>
                        </div>
                    </div>

                    @if ($mentor->bio && mb_strtolower(trim($mentor->bio)) !== 'mentor')
                        <p class="mentor-directory-bio">{{ Illuminate\Support\Str::limit($mentor->bio, 180) }}</p>
                    @else
                        <p class="mentor-directory-bio is-placeholder">Thông tin giới thiệu đang được cập nhật.</p>
                    @endif

                    @if ($mentor->philosophy)
                        <blockquote>“{{ Illuminate\Support\Str::limit($mentor->philosophy, 120) }}”</blockquote>
                    @endif

                    @if ($mentor->certificates->isNotEmpty())
                        <div class="mentor-directory-certificates">
                            <span>Chứng chỉ và thành tích</span>
                            <div>
                                @foreach ($mentor->certificates->take(3) as $certificate)
                                    <button type="button" data-certificate-image="{{ asset('storage/' . $certificate->image) }}" aria-label="Xem chứng chỉ của {{ $mentor->name }}">
                                        <img src="{{ asset('storage/' . $certificate->image) }}" alt="Chứng chỉ {{ $loop->iteration }}">
                                    </button>
                                @endforeach
                                @if ($mentor->certificates->count() > 3)
                                    <small>+{{ $mentor->certificates->count() - 3 }}</small>
                                @endif
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('site.course.index', ['mentor' => $mentor->id]) }}" class="mentor-directory-link">Xem khóa học <span>→</span></a>
                </article>
            @empty
                <div class="mentor-directory-empty"><span>⌕</span><h3>Chưa tìm thấy giảng viên phù hợp</h3><p>Hãy thử tìm theo tên hoặc lĩnh vực chuyên môn khác.</p>@if(request('search'))<a href="{{ route('site.mentor.index') }}">Xem tất cả giảng viên →</a>@endif</div>
            @endforelse
        </div>

        <div class="site-pagination">{{ $mentors->links() }}</div>
    </div>
</section>

<div class="certificate-lightbox" data-certificate-lightbox hidden><button type="button" data-certificate-close aria-label="Đóng ảnh chứng chỉ">×</button><img src="" alt="Ảnh chứng chỉ phóng lớn" data-certificate-preview></div>
@endsection
