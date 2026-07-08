<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ReservationReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerRoomController;
use App\Http\Controllers\Customer\CustomerReservationController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerPromotionController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login/google', [AuthController::class, 'redirectGoogleLogin'])->name('google.login');
    Route::get('/register/google', [AuthController::class, 'redirectGoogleRegister'])->name('google.register');
    Route::get('/login/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
});

Route::middleware('auth')->group(function () {

    Route::middleware('role:admin')->group(function () {

        Route::prefix('admin')->name('admin.')->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            Route::resource('room', RoomController::class);
            Route::resource('room-types', RoomTypeController::class);
            Route::get('room-types/export', [RoomTypeController::class, 'export'])->name('room-types.export');
            Route::get('reservations/data', [ReservationController::class, 'data'])->name('reservations.data');
            Route::get('reservations/available-rooms', [ReservationController::class, 'getAvailableRooms'])->name('reservations.available-rooms');
            Route::get('reservations/validate-promo', [ReservationController::class, 'validatePromo'])->name('reservations.validate-promo');
            Route::patch('reservations/{reservation}/checkin', [ReservationController::class, 'checkin'])->name('reservations.checkin');
            Route::patch('reservations/{reservation}/checkout', [ReservationController::class, 'checkout'])->name('reservations.checkout');
            Route::patch('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
            Route::resource('reservations', ReservationController::class);
            Route::post('facilities/quick-store', [FacilityController::class, 'quickStore'])->name('facilities.quick-store');
            Route::resource('facilities', FacilityController::class);
            Route::get('promotions/export', [PromotionController::class, 'export'])->name('promotions.export');
            Route::resource('promotions', PromotionController::class)
                ->except(['create', 'edit', 'show']);
            Route::get('/reports', [ReservationReportController::class, 'index'])->name('reports');
            Route::get('/reports/export', [ReservationReportController::class, 'export'])->name('reports.export');
            Route::get('/reports/{reservation}/detail', [ReservationReportController::class, 'detail'])->name('reports.detail');

        });

        Route::get('/admin/search', [SearchController::class, 'search'])->name('admin.search');

    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::middleware('role:customer')->group(function () {

        Route::prefix('customer')
            ->name('customer.')
            ->group(function () {

                Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
                    ->name('dashboard');

                Route::get('/rooms', [CustomerRoomController::class, 'index'])
                    ->name('rooms.index');

                Route::get('/reservations', [CustomerReservationController::class, 'index'])
                    ->name('reservations.index');

                Route::post('/reservations', [CustomerReservationController::class, 'store'])
                    ->name('reservations.store');
                Route::patch('/reservations/{reservation}/cancel', [CustomerReservationController::class, 'cancel'])
                     ->name('reservations.cancel');
                Route::post('/reservations/validate-promo', [CustomerReservationController::class, 'validatePromo'])
                      ->name('reservations.validate-promo');
                Route::get('/reservations/available-rooms', [CustomerReservationController::class, 'availableRooms'])
                     ->name('reservations.available-rooms');

                Route::get('/profile', [CustomerProfileController::class, 'index'])
                    ->name('profile');

                Route::put('/profile', [CustomerProfileController::class, 'updateProfile'])
                    ->name('profile.update');

                Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword'])
                    ->name('profile.password.update');

                Route::post('/profile/avatar', [CustomerProfileController::class, 'updateAvatar'])
                    ->name('profile.avatar.update');

                Route::delete('/profile/avatar', [CustomerProfileController::class, 'deleteAvatar'])
                    ->name('profile.avatar.delete');
            });

    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
