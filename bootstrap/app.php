<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cookie token do JavaScript tạo ra không được Laravel giải mã như cookie mã hóa.
        $middleware->encryptCookies(except: ['petclinic_token']);

        $middleware->alias([
            'role' => \App\Http\Middleware\KiemTraVaiTro::class,
            'force.password.change' => \App\Http\Middleware\BatBuocDoiMatKhau::class,
            'web.token' => \App\Http\Middleware\XacThucWebToken::class,
            'web.role' => \App\Http\Middleware\KiemTraVaiTroWeb::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
