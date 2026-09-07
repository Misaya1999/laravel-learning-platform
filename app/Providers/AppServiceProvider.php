<?php

namespace App\Providers;

use App\Models\Announcement;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('Site.Layout.Index', function ($view) {
            $user = auth()->user();
            if (! $user) {
                return $view->with(['headerAnnouncements' => collect(), 'unreadAnnouncementCount' => 0]);
            }

            $published = Announcement::published();
            $headerAnnouncements = (clone $published)
                ->withExists(['reads as is_read' => fn ($query) => $query->where('user_id', $user->id)])
                ->latest('published_at')
                ->limit(8)
                ->get();
            $unreadAnnouncementCount = (clone $published)
                ->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $user->id))
                ->count();

            $view->with(compact('headerAnnouncements', 'unreadAnnouncementCount'));
        });
    }
}
