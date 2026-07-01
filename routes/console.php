<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

Artisan::command('inventory:preflight {--strict : Return non-zero when production checks fail}', function (): int {
    $checks = [];

    $add = static function (string $area, string $check, bool $ok, string $detail = '') use (&$checks): void {
        $checks[] = [
            'area' => $area,
            'check' => $check,
            'status' => $ok ? 'OK' : 'WARN',
            'detail' => $detail,
        ];
    };

    $add('env', 'APP_KEY tersedia', filled(config('app.key')), config('app.key') ? 'configured' : 'missing');
    $add('env', 'APP_DEBUG false untuk production', ! config('app.debug'), 'APP_DEBUG='.(config('app.debug') ? 'true' : 'false'));
    $add('env', 'APP_ENV production untuk go-live', app()->environment('production'), 'APP_ENV='.app()->environment());
    $add('storage', 'storage writable', is_writable(storage_path()), storage_path());
    $add('storage', 'bootstrap/cache writable', is_writable(base_path('bootstrap/cache')), base_path('bootstrap/cache'));

    foreach ([403, 404, 419, 500] as $code) {
        $path = resource_path("views/errors/{$code}.blade.php");
        $add('errors', "error page {$code} tersedia", file_exists($path), $path);
    }

    try {
        DB::connection()->getPdo();
        $add('database', 'database connection', true, DB::connection()->getDatabaseName());
        foreach (['users', 'roles', 'permissions', 'stocks', 'stock_cards', 'audit_logs', 'documents'] as $table) {
            $add('database', "table {$table} tersedia", Schema::hasTable($table), $table);
        }
    } catch (Throwable $exception) {
        $add('database', 'database connection', false, $exception->getMessage());
    }

    foreach (['reports.stock', 'reports.goods-in', 'reports.goods-out', 'reports.stock.export', 'documents.download', 'audit-logs.index'] as $routeName) {
        $add('routes', "route {$routeName} terdaftar", Route::has($routeName), $routeName);
    }

    $composerLock = base_path('composer.lock');
    $lockContent = file_exists($composerLock) ? file_get_contents($composerLock) : '';
    foreach (['spatie/laravel-permission', 'spatie/laravel-activitylog', 'maatwebsite/excel', 'barryvdh/laravel-dompdf'] as $package) {
        $add('packages', "package {$package} locked", Str::contains((string) $lockContent, '"name": "'.$package.'"'), $package);
    }

    $this->table(['Area', 'Check', 'Status', 'Detail'], $checks);

    $failed = collect($checks)->where('status', 'WARN')->count();
    if ($failed > 0) {
        $this->warn("{$failed} preflight check perlu perhatian sebelum production go-live.");
    } else {
        $this->info('Semua preflight check OK.');
    }

    return $this->option('strict') && $failed > 0 ? 1 : 0;
})->purpose('Run Inventory Stock IT pre go-live checks');
