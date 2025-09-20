<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // RateLimiter::for('login', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->input('phone_number'))->response(function (Request $request, array $headers) {
        //         return response('test')->withHeaders($headers);
        //     });
        // });

        Response::macro('okMsg', function (string $messageKey, array $data = [], int $status = 200) {
            return response()->json([
                'success' => true,
                'message' => __($messageKey),
                'data'    => $data,
            ], $status);
        });

        Response::macro('errMsg', function (string $messageKey, array $errors = [], int $status = 400) {
            return response()->json([
                'success' => false,
                'message' => __($messageKey),
                'errors'  => $errors,
            ], $status);
        });
    }
}
