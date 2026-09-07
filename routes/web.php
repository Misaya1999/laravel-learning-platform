<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\AdminMiddleware;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CourseSectionController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\MentorController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\LiveStatusController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\VideoUploadController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Site\CourseController as SiteCourseController;
use App\Http\Controllers\Site\ProfileController as SiteProfileController;
use App\Http\Controllers\Site\EnrollmentController as SiteEnrollmentController;
use App\Http\Controllers\Site\CourseReviewController as SiteCourseReviewController;
use App\Http\Controllers\Site\LearningController as SiteLearningController;
use App\Http\Controllers\Site\CheckoutController as SiteCheckoutController;
use App\Http\Controllers\Site\OrderController as SiteOrderController;
use App\Http\Controllers\Site\PayOSController as SitePayOSController;
use App\Http\Controllers\Site\AnnouncementController as SiteAnnouncementController;
use App\Http\Controllers\Site\BlogController as SiteBlogController;
use App\Http\Controllers\Site\MentorController as SiteMentorController;
use App\Http\Controllers\Auth\GoogleController;

Auth::routes(['verify' => true]);
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::get('/', [HomeController::class, 'index'])->name('site.home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/courses', [SiteCourseController::class, 'index'])->name('site.course.index');
Route::get('/courses/{course}', [SiteCourseController::class, 'show'])->name('site.course.show');
Route::get('/giang-vien', [SiteMentorController::class, 'index'])->name('site.mentor.index');
Route::get('/blog', [SiteBlogController::class, 'index'])->name('site.blog.index');
Route::get('/blog/{slug}', [SiteBlogController::class, 'show'])->name('site.blog.show');
Route::view('/dieu-khoan-su-dung', 'Site.Legal.Terms')->name('site.legal.terms');
Route::view('/chinh-sach-bao-mat', 'Site.Legal.Privacy')->name('site.legal.privacy');
Route::view('/chinh-sach-thanh-toan', 'Site.Legal.Payment')->name('site.legal.payment');
Route::post('/payments/payos/webhook', [SitePayOSController::class, 'webhook'])->name('site.payos.webhook');

Route::middleware('auth')->group(function () {
    Route::patch('/notifications/read-all', [SiteAnnouncementController::class, 'readAll'])->name('site.announcements.read-all');
    Route::patch('/notifications/{announcement}/read', [SiteAnnouncementController::class, 'read'])->name('site.announcements.read');
    Route::get('/profile', [SiteProfileController::class, 'edit'])->name('site.profile.edit');
    Route::put('/profile', [SiteProfileController::class, 'update'])->name('site.profile.update');
    Route::get('/my-courses', [SiteEnrollmentController::class, 'index'])->name('site.my-courses.index');
    Route::get('/checkout', [SiteCheckoutController::class, 'show'])->name('site.checkout.show');
    Route::get('/orders', [SiteOrderController::class, 'index'])->name('site.orders.index');
    Route::get('/orders/{order}', [SiteOrderController::class, 'show'])->name('site.orders.show');
    Route::patch('/orders/{order}/cancel', [SiteOrderController::class, 'cancel'])->name('site.orders.cancel');
    Route::post('/orders/{order}/receipt', [SiteOrderController::class, 'uploadReceipt'])->name('site.orders.receipt.store');
    Route::get('/orders/{order}/receipt', [SiteOrderController::class, 'receipt'])->name('site.orders.receipt.show');
    Route::get('/orders/{order}/status', [SiteOrderController::class, 'status'])->name('site.orders.status');
    Route::post('/orders/{order}/payos/retry', [SitePayOSController::class, 'retry'])->name('site.payos.retry');
    Route::patch('/orders/{order}/payos/fallback', [SitePayOSController::class, 'useBankTransfer'])->name('site.payos.fallback');
    Route::get('/payments/payos/return', [SitePayOSController::class, 'result'])->name('site.payos.return');
    Route::get('/payments/payos/cancel', [SitePayOSController::class, 'cancel'])->name('site.payos.cancel');

    Route::middleware('verified')->group(function () {
        Route::get('/my-courses/{course}/learn/{lesson?}', [SiteLearningController::class, 'show'])->name('site.learning.show');
        Route::get('/my-courses/{course}/lessons/{lesson}/attachment', [SiteLearningController::class, 'attachment'])
            ->middleware(['signed', 'throttle:120,1'])
            ->name('site.learning.attachment');
        Route::get('/my-courses/{course}/lessons/{lesson}/video', [SiteLearningController::class, 'video'])->name('site.learning.video');
        Route::post('/my-courses/{course}/lessons/{lesson}/progress', [SiteLearningController::class, 'progress'])->name('site.learning.progress');
        Route::post('/courses/{course}/review', [SiteCourseReviewController::class, 'store'])->name('site.course.review');
        Route::post('/blog/{post}/rating', [SiteBlogController::class, 'rate'])->middleware('throttle:20,1')->name('site.blog.rating');
        Route::post('/blog/{post}/comments', [SiteBlogController::class, 'comment'])->middleware('throttle:10,1')->name('site.blog.comment');
        Route::post('/checkout', [SiteCheckoutController::class, 'store'])->name('site.checkout.store');
    });
});

Route::prefix('admin')->middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('Admin.Dashboard');

    Route::get('/profile', [ProfileController::class, 'profile'])->name('Admin.Profile');
    Route::post('/profile', [ProfileController::class, 'profile_update'])->name('Admin.Profile.Update');

    Route::get('/category', [CategoryController::class, 'category'])->name('Admin.Category');
    Route::post('/category', [CategoryController::class, 'store'])->name('Admin.Category.Store');
    Route::put('/category/{category}', [CategoryController::class, 'update'])->name('Admin.Category.Update');
    Route::delete('/category/{category}', [CategoryController::class, 'destroy'])->name('Admin.Category.Delete');

    Route::get('/mentor', [MentorController::class, 'index'])->name('Admin.Mentor');
    Route::post('/mentor', [MentorController::class, 'store'])->name('Admin.Mentor.Store');
    Route::put('/mentor/{mentor}', [MentorController::class, 'update'])->name('Admin.Mentor.Update');
    Route::delete('/mentor/{mentor}', [MentorController::class, 'destroy'])->name('Admin.Mentor.Delete');
    Route::delete('/mentor/certificate/{certificate}', [MentorController::class, 'destroyCertificate'])->name('Admin.Mentor.Certificate.Delete');

    Route::get('/course', [CourseController::class, 'course'])->name('Admin.Course');
    Route::get('/course/{course}/detail', [CourseController::class,'detail'])->name('Admin.Course.Detail');
    Route::post('/course', [CourseController::class, 'store'])->name('Admin.Course.Store');
    Route::put('/course/{course}', [CourseController::class, 'update'])->name('Admin.Course.Update');
    Route::delete('/course/{course}', [CourseController::class, 'destroy'])->name('Admin.Course.Delete');

    Route::get('/blog', [AdminBlogController::class, 'index'])->name('Admin.Blog');
    Route::post('/blog', [AdminBlogController::class, 'store'])->name('Admin.Blog.Store');
    Route::post('/blog/upload-image', [AdminBlogController::class, 'uploadImage'])->name('Admin.Blog.Image.Upload');
    Route::put('/blog/{post}', [AdminBlogController::class, 'update'])->name('Admin.Blog.Update');
    Route::delete('/blog/{post}', [AdminBlogController::class, 'destroy'])->name('Admin.Blog.Delete');
    Route::patch('/blog-comments/{comment}/visibility', [AdminBlogController::class, 'toggleComment'])->name('Admin.Blog.Comment.Visibility');
    Route::delete('/blog-comments/{comment}', [AdminBlogController::class, 'destroyComment'])->name('Admin.Blog.Comment.Delete');
    Route::delete('/blog-ratings/{rating}', [AdminBlogController::class, 'destroyRating'])->name('Admin.Blog.Rating.Delete');

    Route::post('/course/{course}/section', [CourseSectionController::class, 'store'])->name('Admin.Course.Section.Store');
    Route::put('/course/section/{section}', [CourseSectionController::class, 'update'])->name('Admin.Course.Section.Update');
    Route::delete('/course/section/{section}', [CourseSectionController::class, 'destroy'])->name('Admin.Course.Section.Delete');
    
    Route::post('/course/section/{section}/lesson', [LessonController::class,'store',])->name('Admin.Course.Lesson.Store');
    Route::post('/course/lesson/video-upload/chunk', [VideoUploadController::class, 'chunk'])->name('Admin.Course.Lesson.Video.Chunk');
    Route::post('/course/lesson/video-upload/complete', [VideoUploadController::class, 'complete'])->name('Admin.Course.Lesson.Video.Complete');
    Route::put('/course/lesson/{lesson}', [LessonController::class, 'update'])->name('Admin.Course.Lesson.Update');
    Route::delete('/course/lesson/{lesson}', [LessonController::class, 'destroy'])->name('Admin.Course.Lesson.Delete');
    Route::get('/course/lesson/{lesson}/attachment', [LessonController::class, 'attachment'])->name('Admin.Course.Lesson.Attachment');

    Route::get('/user', [UserController::class, 'user'])->name('Admin.User');
    Route::get('/live-status', LiveStatusController::class)->name('Admin.LiveStatus');
    Route::patch('/user/{user}/status', [UserController::class, 'updateStatus'])->name('Admin.User.Status');
    Route::post('/user/{user}/password-reset', [UserController::class, 'sendPasswordReset'])->name('Admin.User.PasswordReset');
    Route::delete('/user/{user}', [UserController::class, 'user_delete'])->name('Admin.User.Delete');

    Route::get('/order', [OrderController::class, 'index'])->name('Admin.Order');
    Route::patch('/order/{order}/status', [OrderController::class, 'updateStatus'])->name('Admin.Order.Status');
    Route::get('/order/{order}/receipt', [OrderController::class, 'receipt'])->name('Admin.Order.Receipt');

    Route::get('/review', [ReviewController::class, 'index'])->name('Admin.Review');
    Route::patch('/review/{review}/visibility', [ReviewController::class, 'toggleVisibility'])->name('Admin.Review.Visibility');
    Route::delete('/review/{review}', [ReviewController::class, 'destroy'])->name('Admin.Review.Delete');

    Route::get('/announcement', [AnnouncementController::class, 'index'])->name('Admin.Announcement');
    Route::post('/announcement', [AnnouncementController::class, 'store'])->name('Admin.Announcement.Store');
    Route::put('/announcement/{announcement}', [AnnouncementController::class, 'update'])->name('Admin.Announcement.Update');
    Route::delete('/announcement/{announcement}', [AnnouncementController::class, 'destroy'])->name('Admin.Announcement.Delete');

});
