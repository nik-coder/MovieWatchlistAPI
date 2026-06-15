<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeUriPath
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->getPathInfo();

        if (str_contains($path, '//')) {
            $normalized = preg_replace('#/+#', '/', $path);

            if ($normalized !== null && $normalized !== $path) {
                $query = $request->getQueryString();
                $target = $normalized.(empty($query) ? '' : '?'.$query);

                return redirect($target, 308);
            }
        }

        return $next($request);
    }
}
