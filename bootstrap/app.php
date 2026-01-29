<?php

/**
 * Laravel Application Bootstrap
 * 
 * Implements the Service Container pattern for dependency injection
 * and IoC (Inversion of Control) container initialization.
 * This bootstrap file orchestrates the application lifecycle management.
 *
 * @package HabibiStay
 * @version 1.0.0
 */

/*
|--------------------------------------------------------------------------
| Create The Application Instance
|--------------------------------------------------------------------------
|
| Instantiate the Laravel application container implementing the 
| Service Locator and Dependency Injection patterns. This container
| serves as the central registry for all application services.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Register core service providers and bind interfaces to concrete
| implementations. This follows the Interface Segregation Principle
| and enables polymorphic behavior throughout the application.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| Return the configured application instance to the calling script.
| This instance will be used to handle incoming HTTP requests or
| console commands throughout the application lifecycle.
|
*/

return $app;
