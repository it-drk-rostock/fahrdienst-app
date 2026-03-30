<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    // Nur die IP der Sophos Firewall angeben
    $middleware->trustProxies(at: '192.168.253.254');
        })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
