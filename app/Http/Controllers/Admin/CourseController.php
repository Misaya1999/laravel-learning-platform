<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use App\Models\Mentor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function course(Request $request)
    {
        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['latest', 'oldest', 'name_asc', 'name_desc', 'price_asc', 'price_desc', 'students_desc', 'rating_desc'], true)
            ? $sort
            : 'latest';

        $courseQuery = Course::with(['category', 'mentors'])->withLearningStats();

        if (in_array($request->status, [Course::STATUS_DRAFT, Course::STATUS_PUBLISHED, Course::STATUS_HIDDEN], true)) {
            $courseQuery->where('status', $request->status);
        }

        if ($search = trim($request->string('search')->toString())) {
            $courseQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('mentors', fn ($mentor) => $mentor->where('name', 'like', "%{$search}%"));
            });
        }

        match ($sort) {
            'oldest' => $courseQuery->oldest(),
            'name_asc' => $courseQuery->orderBy('name'),
            'name_desc' => $courseQuery->orderByDesc('name'),
            'price_asc' => $courseQuery->orderBy('price'),
            'price_desc' => $courseQuery->orderByDesc('price'),
            'students_desc' => $courseQuery->orderByDesc('students_count'),
            'rating_desc' => $courseQuery->orderByDesc('reviews_avg_rating'),
            default => $courseQuery->latest(),
        };

        $course = $courseQuery->paginate(15)->withQueryString();

        $category = Category::orderBy('name')->get();
        $mentor = Mentor::orderBy('name')->get();

        return view('Admin.Course', compact('course', 'category', 'mentor'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCourse($request);
        $mentorIds = $data['mentor_ids'] ?? [];
        unset($data['mentor_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads/course', 'public');
        }

        $course = Course::create($data);
        $course->mentors()->sync($mentorIds);

        return back()->with('success', 'Thêm khóa học thành công.');
    }

    public function update(Request $request, Course $course)
    {
        $data = $this->validateCourse($request);
        $mentorIds = $data['mentor_ids'] ?? [];
        unset($data['mentor_ids']);

        if ($request->hasFile('image')) {
            if ($course->image) {
                Storage::disk('public')->delete($course->image);
            }

            $data['image'] = $request->file('image')->store('uploads/course', 'public');
        }

        $course->update($data);
        $course->mentors()->sync($mentorIds);

        return back()->with('success', 'Cập nhật khóa học thành công.');
    }

    public function destroy(Course $course)
    {
        $attachments = $course->sections()
            ->with('lessons:id,course_section_id,attachment')
            ->get()
            ->flatMap(fn ($section) => $section->lessons->pluck('attachment'))
            ->filter()
            ->values()
            ->all();
        $image = $course->image;

        $course->delete();

        if ($attachments) {
            Storage::disk('local')->delete($attachments);
            Storage::disk('public')->delete($attachments);
        }
        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return back()->with('success', 'Xóa khóa học thành công.');
    }

    public function detail(Course $course)
    {
        $course->load(['category', 'mentors', 'sections.lessons']);

        return view('Admin.CourseDetail', compact('course'));
    }

    private function validateCourse(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|exists:category,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:9999999999.99',
            'access_duration' => 'required|integer|min:1|max:3650',
            'access_duration_unit' => 'required|in:days,months',
            'status' => 'required|in:draft,published,hidden',
            'mentor_ids' => 'nullable|array',
            'mentor_ids.*' => 'integer|distinct|exists:mentors,id',
        ]);
    }
}
