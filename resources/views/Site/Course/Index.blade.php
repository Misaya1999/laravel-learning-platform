@extends('Site.Layout.Index')

@section('title', 'Tất cả khóa học — KhoaHocPlus')

@section('content')
    @php
        $categoryKey = mb_strtolower(Illuminate\Support\Str::ascii($selectedCategory?->name ?? ''));
        $catalogTheme = str_contains($categoryKey, 'kinh te') ? 'economy' : (str_contains($categoryKey, 'lap trinh') ? 'programming' : (str_contains($categoryKey, 'ai') || str_contains($categoryKey, 'tri tue') ? 'ai' : 'general'));
        $catalogView = request('view') === 'list' ? 'list' : 'grid';
    @endphp
    <section class="page-hero course-catalog-hero catalog-theme-{{ $catalogTheme }}">
        <div class="site-container page-hero-grid">
            <div><span class="eyebrow"><i></i> {{ $selectedCategory ? 'Chủ đề ' . $selectedCategory->name : 'Thư viện kiến thức' }}</span><h1>{{ $selectedCategory ? $selectedCategory->name : 'Tìm khóa học' }}<br><em>phù hợp với bạn.</em></h1></div>
            <div class="catalog-hero-copy"><span class="catalog-theme-mark" aria-hidden="true"></span><p>Khám phá các khóa học theo chủ đề, học theo tốc độ của riêng bạn và quay lại bất cứ lúc nào.</p></div>
        </div>
    </section>

    <section class="catalog-section">
        <div class="site-container">
            <div class="category-chips" aria-label="Lọc theo danh mục">
                <a href="{{ route('site.course.index', array_filter(['search' => request('search'), 'mentor' => request('mentor'), 'sort' => $sort, 'view' => $catalogView])) }}" class="{{ ! $selectedCategory ? 'active' : '' }}">Tất cả <span>{{ $category->sum('courses_count') }}</span></a>
                @foreach ($category as $categoryItem)
                    <a href="{{ route('site.course.index', array_filter(['category' => $categoryItem->id, 'search' => request('search'), 'mentor' => request('mentor'), 'sort' => $sort, 'view' => $catalogView])) }}" class="{{ $selectedCategory?->id === $categoryItem->id ? 'active' : '' }}">{{ $categoryItem->name }} <span>{{ $categoryItem->courses_count }}</span></a>
                @endforeach
            </div>

            <form action="{{ route('site.course.index') }}" method="GET" class="catalog-toolbar" data-catalog-form>
                @if ($selectedCategory)<input type="hidden" name="category" value="{{ $selectedCategory->id }}">@endif
                @if (request('mentor'))<input type="hidden" name="mentor" value="{{ request('mentor') }}">@endif
                <input type="hidden" name="view" value="{{ $catalogView }}">
                <label class="search-field">
                    <span>⌕</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Tìm tên khóa học...">
                </label>
                <select name="sort" onchange="this.form.submit()" aria-label="Sắp xếp khóa học">
                    <option value="latest" @selected($sort === 'latest')>Mới nhất</option>
                    <option value="rating" @selected($sort === 'rating')>Đánh giá cao nhất</option>
                    <option value="students" @selected($sort === 'students')>Nhiều học viên nhất</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>Giá thấp đến cao</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Giá cao đến thấp</option>
                </select>
                <button type="submit" class="primary-button">Tìm kiếm</button>
            </form>

            <div class="catalog-result-bar">
                <div class="catalog-summary"><strong>{{ $course->total() }}</strong> khóa học được tìm thấy</div>
                <div class="catalog-view-switch" aria-label="Kiểu hiển thị">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="{{ $catalogView === 'grid' ? 'active' : '' }}" aria-label="Dạng lưới">▦</a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" class="{{ $catalogView === 'list' ? 'active' : '' }}" aria-label="Dạng danh sách">☷</a>
                </div>
            </div>
            <div class="catalog-loading" data-catalog-loading aria-hidden="true"><i></i><i></i><i></i></div>
            <div class="course-grid catalog-grid catalog-grid-{{ $catalogView }}" data-catalog-results>
                @forelse ($course as $courseItem)
                    @include('Site.Partials.CourseCard', ['courseItem' => $courseItem])
                @empty
                    <div class="empty-panel catalog-empty"><span class="empty-illustration">⌕</span><h3>Chưa tìm thấy khóa học phù hợp</h3><p>Hãy thử từ khóa ngắn hơn hoặc chọn một chủ đề khác.</p><a href="{{ route('site.course.index') }}" class="arrow-link">Xem tất cả khóa học →</a></div>
                @endforelse
            </div>
            <div class="site-pagination">{{ $course->links() }}</div>
        </div>
    </section>
@endsection
