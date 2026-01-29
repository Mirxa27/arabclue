<?php

/**
 * HabibiStay Platform - Public Entry Point
 * 
 * Implements the Front Controller architectural pattern for unified
 * request handling. This entry point enforces security boundaries,
 * initializes the application container, and delegates request
 * processing to the HTTP kernel.
 *
 * @package HabibiStay
 * @version 1.0.0
 * @author HabibiStay Development Team
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| Implement maintenance mode detection using file-based flagging.
| This provides zero-downtime deployment capabilities and graceful
| service degradation during updates.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Leverage Composer's PSR-4 autoloader for lazy class loading.
| This implements the Lazy Initialization pattern for optimal
| memory utilization and performance optimization.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Bootstrap The Application Container
|--------------------------------------------------------------------------
|
| Initialize the Service Container implementing Dependency Injection
| and Inversion of Control principles. This provides a centralized
| registry for service resolution and lifecycle management.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Handle The Incoming Request
|--------------------------------------------------------------------------
|
| Resolve the HTTP kernel from the IoC container and process the
| incoming request through the middleware pipeline. This implements
| the Chain of Responsibility pattern for extensible request handling.
|
*/

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

/*
|--------------------------------------------------------------------------
| Terminate The Application
|--------------------------------------------------------------------------
|
| Execute post-response lifecycle hooks and cleanup operations.
| This ensures proper resource deallocation, connection pooling,
| and graceful shutdown procedures.
|
*/

$kernel->terminate($request, $response);
