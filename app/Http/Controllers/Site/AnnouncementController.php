<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function read(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless(Announcement::published()->whereKey($announcement->getKey())->exists(), 404);
        AnnouncementRead::updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $request->user()->id],
            ['read_at' => now()],
        );

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $now = now();
        $rows = Announcement::published()->pluck('id')->map(fn ($id) => [
            'announcement_id' => $id,
            'user_id' => $request->user()->id,
            'read_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();
        if ($rows) AnnouncementRead::upsert($rows, ['announcement_id', 'user_id'], ['read_at', 'updated_at']);

        return back();
    }
}
