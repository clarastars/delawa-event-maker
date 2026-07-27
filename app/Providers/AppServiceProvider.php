<?php

namespace App\Providers;

use App\Contracts\GiftCardBalance;
use App\Contracts\Otp;
use App\Services\Otp\AuthenticaSa;
use App\Services\Otp\DebugOtp;
use App\Services\Tsepass\GiftCardBalance as TsepassGiftCardBalance;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Otp::class, config('services.authentica.debug_otp') ? DebugOtp::class : AuthenticaSa::class);
        $this->app->bind(GiftCardBalance::class, TsepassGiftCardBalance::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
