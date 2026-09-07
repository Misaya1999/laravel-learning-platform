<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $announcements = Announcement::query()
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', '%' . $request->string('search')->trim() . '%')
                ->orWhere('content', 'like', '%' . $request->string('search')->trim() . '%')))
            ->when($request->status === 'published', fn ($query) => $query->where('is_published', true))
            ->when($request->status === 'draft', fn ($query) => $query->where('is_published', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('Admin.Announcement', compact('announcements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['published_at'] = $this->publishedAt($data['published_at'] ?? null);
        $data['is_published'] = $request->boolean('is_published');
        if ($data['is_published'] && empty($data['published_at'])) $data['published_at'] = now();
        Announcement::create($data);

        return back()->with('success', 'Đã tạo thông báo.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validated($request);
        $data['published_at'] = $this->publishedAt($data['published_at'] ?? null);
        $data['is_published'] = $request->boolean('is_published');
        if ($data['is_published'] && empty($data['published_at'])) $data['published_at'] = $announcement->published_at ?? now();
        $announcement->update($data);

        return back()->with('success', 'Đã cập nhật thông báo.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Đã xóa thông báo.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'in:info,success,warning'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function publishedAt(?string $value): ?Carbon
    {
        return $value
            ? Carbon::parse($value, config('app.display_timezone'))->utc()
            : null;
    }
}
