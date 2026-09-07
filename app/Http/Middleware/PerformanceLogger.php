<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('perf_start', microtime(true));
        $request->attributes->set('perf_queries', []);

        DB::listen(function ($query) use ($request): void {
            $queries = $request->attributes->get('perf_queries', []);
            $queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];
            $request->attributes->set('perf_queries', $queries);
        });

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($request->path() === 'up') {
            return;
        }

        $start = $request->attributes->get('perf_start', microtime(true));
        $totalMs = (microtime(true) - $start) * 1000;

        $queries = $request->attributes->get('perf_queries', []);
        $count = count($queries);
        $maxMs = $queries ? max(array_column($queries, 'time')) : 0;

        $context = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user' => auth()->check() ? auth()->id() : null,
            'db_queries' => $count,
            'max_query_ms' => round($maxMs, 1),
            'total_ms' => round($totalMs, 1),
        ];

        $slowQueries = array_values(array_filter($queries, fn (array $q): bool => $q['time'] > 500));

        if (!empty($slowQueries)) {
            $context['slow_queries'] = array_map(fn (array $q): array => [
                'sql' => $q['sql'],
                'bindings' => $q['bindings'],
                'duration_ms' => round($q['time'], 1),
            ], $slowQueries);
        }

        Log::info('Request performance', $context);
    }
}
