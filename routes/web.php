<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\ContactUploadController;
use App\Http\Controllers\Admin\EventClosureController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\VoucherUploadController;
use App\Http\Controllers\EventEndedController;
use App\Http\Controllers\EventInviteController;
use App\Http\Middleware\EnsureEventIsOpen;
use Illuminate\Support\Facades\Route;

Route::get('/', EventEndedController::class)->name('home');
Route::redirect('/accept', '/');
Route::any('/accept/{any?}', fn () => redirect('/'))->where('any', '.*');

Route::prefix('e/{event:slug}')->name('event.')->middleware(EnsureEventIsOpen::class)->group(function (): void {
    Route::get('/', [EventInviteController::class, 'index'])->name('invite');
    Route::post('/otp/send', [EventInviteController::class, 'sendOtp'])
        ->middleware('throttle:6,1')
        ->name('otp.send');
    Route::post('/otp/verify', [EventInviteController::class, 'verifyOtp'])
        ->middleware('throttle:6,1')
        ->name('otp.verify');
    Route::post('/otp/resend', [EventInviteController::class, 'resendOtp'])
        ->middleware('throttle:3,1')
        ->name('otp.resend');
    Route::post('/otp/cancel', [EventInviteController::class, 'cancelOtp'])->name('otp.cancel');
    Route::get('/vouchers', [EventInviteController::class, 'showVouchers'])->name('vouchers');
    Route::post('/vouchers/review', [EventInviteController::class, 'submitReview'])->name('vouchers.review');
    Route::post('/products/{product}/claim', [EventInviteController::class, 'claimProduct'])->name('products.claim');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::redirect('/', '/admin/events')->name('dashboard');

        Route::resource('events', EventController::class)->except(['edit']);
        Route::post('/events/{event}/banner', [EventController::class, 'updateBanner'])->name('events.banner.update');
        Route::get('/events/{event}/current-report', [EventController::class, 'currentReport'])->name('events.current-report');
        Route::get('/events/{event}/current-report/statement', [EventController::class, 'downloadStatement'])->name('events.current-report.statement');
        Route::get('/events/{event}/close', [EventClosureController::class, 'create'])->name('events.close.create');
        Route::post('/events/{event}/close', [EventClosureController::class, 'store'])->name('events.close.store');
        Route::get('/events/{event}/closure', [EventClosureController::class, 'show'])->name('events.closure.show');
        Route::get('/events/{event}/closure/pdf', [EventClosureController::class, 'downloadPdf'])->name('events.closure.pdf');
        Route::post('/events/{event}/closure/pdf', [EventClosureController::class, 'regeneratePdf'])->name('events.closure.pdf.regenerate');
        Route::get('/events/{event}/closure/register', [EventClosureController::class, 'downloadRegister'])->name('events.closure.register');

        Route::resource('events.products', ProductController::class)->except(['index', 'show']);

        Route::get('/vouchers/upload', [VoucherUploadController::class, 'create'])->name('vouchers.upload.create');
        Route::get('/vouchers/upload/sample', [VoucherUploadController::class, 'sample'])->name('vouchers.upload.sample');
        Route::post('/vouchers/upload', [VoucherUploadController::class, 'store'])->name('vouchers.upload.store');
        Route::resource('vouchers', VoucherController::class)->except(['show']);

        Route::get('/contacts/upload', [ContactUploadController::class, 'create'])->name('contacts.upload.create');
        Route::get('/contacts/upload/sample', [ContactUploadController::class, 'sample'])->name('contacts.upload.sample');
        Route::post('/contacts/upload', [ContactUploadController::class, 'store'])->name('contacts.upload.store');
        Route::post('/contacts', [ContactUploadController::class, 'storeForm'])->name('contacts.store');
        Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
        Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
        Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
        Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
        Route::post('/contacts/{contact}/assign-voucher', [ContactController::class, 'assignVoucher'])->name('contacts.assign-voucher');
        Route::delete('/contacts/{contact}/vouchers/{voucher}', [ContactController::class, 'unassignVoucher'])->name('contacts.unassign-voucher');

        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    });
});
