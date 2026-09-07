<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use App\Models\MentorCertificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MentorController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['latest', 'oldest', 'name_asc', 'name_desc', 'courses_desc'], true)
            ? $sort
            : 'latest';

        $mentorQuery = Mentor::with('certificates')->withCount('courses')->orderByDesc('is_main');

        if ($search = trim($request->string('search')->toString())) {
            $mentorQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        match ($sort) {
            'oldest' => $mentorQuery->oldest(),
            'name_asc' => $mentorQuery->orderBy('name'),
            'name_desc' => $mentorQuery->orderByDesc('name'),
            'courses_desc' => $mentorQuery->orderByDesc('courses_count'),
            default => $mentorQuery->latest(),
        };

        $mentor = $mentorQuery->paginate(15)->withQueryString();

        return view('Admin.Mentor', compact('mentor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateMentor($request);
        $data['is_main'] = $request->boolean('is_main');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('uploads/mentor', 'public');
        }

        $mentor = Mentor::create($data);
        $this->storeCertificates($request, $mentor);
        if ($mentor->is_main) Mentor::whereKeyNot($mentor->id)->update(['is_main' => false]);

        return back()->with('success', 'Thêm mentor thành công.');
    }

    public function update(Request $request, Mentor $mentor): RedirectResponse
    {
        $data = $this->validateMentor($request, $mentor);
        $data['is_main'] = $request->boolean('is_main');

        if ($request->hasFile('avatar')) {
            if ($mentor->avatar) {
                Storage::disk('public')->delete($mentor->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('uploads/mentor', 'public');
        }

        $mentor->update($data);
        $this->storeCertificates($request, $mentor);
        if ($mentor->is_main) Mentor::whereKeyNot($mentor->id)->update(['is_main' => false]);

        return back()->with('success', 'Cập nhật mentor thành công.');
    }

    public function destroy(Mentor $mentor): RedirectResponse
    {
        $mentor->load('certificates');
        $avatar = $mentor->avatar;
        $certificateImages = $mentor->certificates->pluck('image')->all();
        $mentor->delete();

        if ($avatar) {
            Storage::disk('public')->delete($avatar);
        }
        if ($certificateImages) Storage::disk('public')->delete($certificateImages);

        return back()->with('success', 'Xóa mentor thành công.');
    }

    public function destroyCertificate(MentorCertificate $certificate): RedirectResponse
    {
        $image = $certificate->image;
        $certificate->delete();
        Storage::disk('public')->delete($image);

        return back()->with('success', 'Đã xóa ảnh chứng chỉ.');
    }

    private function validateMentor(Request $request, ?Mentor $mentor = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:mentors,email,' . ($mentor?->id ?? 'NULL')],
            'specialty' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'philosophy' => ['nullable', 'string', 'max:2000'],
            'credentials' => ['nullable', 'string', 'max:4000'],
            'is_main' => ['nullable', 'boolean'],
            'certificates' => ['nullable', 'array', 'max:10'],
            'certificates.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);
    }

    private function storeCertificates(Request $request, Mentor $mentor): void
    {
        $nextPosition = ($mentor->certificates()->max('position') ?? -1) + 1;
        foreach ($request->file('certificates', []) as $index => $image) {
            $mentor->certificates()->create([
                'image' => $image->store('uploads/mentor/certificates', 'public'),
                'position' => $nextPosition + $index,
            ]);
        }
    }
}
