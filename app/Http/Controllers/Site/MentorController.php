<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Mentor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MentorController extends Controller
{
    public function index(Request $request): View
    {
        $mentors = Mentor::query()
            ->with('certificates')
            ->withCount(['courses' => fn ($query) => $query->where('status', Course::STATUS_PUBLISHED)])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%"));
            })
            ->orderByDesc('is_main')
            ->orderByDesc('courses_count')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('Site.Mentor.Index', compact('mentors'));
    }
}
