<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseSectionController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $position = $course->sections()
            ->max('position');

        $course->sections()->create([
            'title' => $data['title'],
            'position' => ($position ?? 0) + 1,
        ]);

        return back()->with(
            'success',
            'Thêm chương thành công.'
        );
    }

    public function update(
        Request $request,
        CourseSection $section
    ) {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'position' => 'required|integer|min:1',
        ]);

        $section->update($data);

        return back()->with(
            'success',
            'Cập nhật chương thành công.'
        );
    }

    public function destroy(CourseSection $section)
    {
        $files = $section->lessons()
            ->whereNotNull('attachment')
            ->pluck('attachment')
            ->all();

        $section->delete();

        if ($files) {
            Storage::disk('local')->delete($files);
            Storage::disk('public')->delete($files);
        }

        return back()->with(
            'success',
            'Xóa chương thành công.'
        );
    }
}
