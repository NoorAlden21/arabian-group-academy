<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Arr;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // اجلب من الكونفيغ وثبّت إنو Array
        /** @var array<int, string> $supported */
        $supported = Arr::wrap(config('localization.supported', ['en', 'ar']));

        $default = (string) config('localization.default', 'en');

        // أولوية Query (?lang=ar) ثم Accept-Language
        $explicit  = $request->query('lang'); // قد تكون null أو string
        $preferred = $request->getPreferredLanguage($supported); // ?string

        $locale = $explicit ?: $preferred ?: $default;

        if (!in_array($locale, $supported, true)) {
            $locale = $default;
        }

        App::setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', App::getLocale());

        return $response;
    }
}
