@php
    $palette = ['coral', 'blue', 'lime', 'violet', 'cyan'];
    $categorySeed = max(1, (int) ($courseItem->category_id ?? $courseItem->id));
    $tone = $palette[($categorySeed - 1) % count($palette)];
    $categorySymbols = ['✦', '⌘', '↗', '◎', '△'];
    $fallbackSymbol = $categorySymbols[($categorySeed - 1) % count($categorySymbols)];
    $categoryKey = mb_strtolower(Illuminate\Support\Str::ascii($courseItem->category?->name ?? ''));
    $topicTheme = str_contains($categoryKey, 'kinh te') ? 'economy' : (str_contains($categoryKey, 'lap trinh') ? 'programming' : (str_contains($categoryKey, 'ai') || str_contains($categoryKey, 'tri tue') ? 'ai' : 'general'));
    $isOwned = isset($ownedCourseIds) && $ownedCourseIds->contains($courseItem->id);
@endphp

<article class="course-card">
    <a href="{{ route('site.course.show', $courseItem) }}" class="course-visual course-visual-{{ $tone }} course-pattern-{{ $topicTheme }}">
        <span class="course-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
        <span class="course-symbol" aria-hidden="true">{{ $fallbackSymbol }}</span>
        @if ($courseItem->image)
            <img src="{{ asset('storage/' . $courseItem->image) }}" alt="{{ $courseItem->name }}" data-course-image>
        @endif
        <span class="course-category">{{ $courseItem->category?->name ?? 'Khóa học' }}</span>
    </a>
    <div class="course-card-body">
        <div class="course-meta">
            <span>{{ $courseItem->lessons_count ?? $courseItem->lessons->count() }} bài học</span>
            <span>◷ Truy cập {{ $courseItem->accessPeriodLabel() }}</span>
            <span>★ {{ $courseItem->reviews_avg_rating ? number_format($courseItem->reviews_avg_rating, 1) : 'Chưa có' }}</span>
            <span>♙ {{ $courseItem->students_count ?? 0 }} học viên</span>
        </div>
        <h3><a href="{{ route('site.course.show', $courseItem) }}">{{ $courseItem->name }}</a></h3>
        @if ($courseItem->mentors->isNotEmpty())
            <div class="course-mentor-names">
                <span class="course-mentor-label">Giảng viên</span>
                <span class="course-mentor-mini-avatar">
                    @if ($courseItem->mentors->first()->avatar)
                        <img src="{{ asset('storage/' . $courseItem->mentors->first()->avatar) }}" alt="">
                    @else
                        {{ mb_strtoupper(mb_substr($courseItem->mentors->first()->name, 0, 1)) }}
                    @endif
                </span>
                <strong>{{ $courseItem->mentors->pluck('name')->join(', ') }}</strong>
            </div>
        @endif
        <p>{{ Illuminate\Support\Str::limit($courseItem->description ?: 'Nội dung được xây dựng theo lộ trình rõ ràng, dễ theo dõi.', 92) }}</p>
        <div class="course-card-footer">
            @if ($isOwned)
                <a href="{{ route('site.learning.show', $courseItem) }}" class="course-owned-link">
                    <span>Đã sở hữu khóa học</span><strong>Vào học ngay →</strong>
                </a>
            @else
                <strong>{{ (float) $courseItem->price <= 0 ? 'Miễn phí' : number_format($courseItem->price, 0, ',', '.') . 'đ' }}</strong>
                @auth
                    <button type="button" class="add-cart-button" data-add-cart data-course-id="{{ $courseItem->id }}" data-course-name="{{ $courseItem->name }}" data-course-price="{{ (float) $courseItem->price }}" data-course-url="{{ route('site.course.show', $courseItem) }}" aria-label="Thêm {{ $courseItem->name }} vào giỏ">+</button>
                @else
                    <a href="{{ route('login') }}" class="add-cart-button" aria-label="Đăng nhập để đăng ký {{ $courseItem->name }}">→</a>
                @endauth
            @endif
        </div>
    </div>
</article>
