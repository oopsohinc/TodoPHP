<?php
/**
 * Entry Point (Front Controller)
 * 
 * This is the ONLY file that gets executed directly.
 * All requests go through this file thanks to .htaccess
 * 
 * MVC Flow:
 * 1. User visits URL (e.g., /tasks/create)
 * 2. .htaccess redirects to this file
 * 3. This file loads Composer autoloader
 * 4. This file creates Router and defines routes
 * 5. Router dispatches to appropriate Controller
 * 6. Controller uses Model (if needed) and loads View
 * 7. HTML is rendered to browser
 */

// No need to call session_start() here!
// The Session class handles it automatically with proper checks
// This prevents "headers already sent" errors

// Load Composer's autoloader
// This allows us to use classes without manually requiring files
require_once __DIR__ . '/../vendor/autoload.php';

// Import classes we need
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\TaskController;

// Create a new Router instance
$router = new Router();

/**
 * Define Routes
 * 
 * Format: $router->method('path', ControllerClass, 'methodName')
 * 
 * GET routes: For displaying pages
 * POST routes: For form submissions
 */

// Authentication Routes
$router->get('/login', AuthController::class, 'login');
$router->post('/login', AuthController::class, 'login');
$router->get('/register', AuthController::class, 'register');
$router->post('/register', AuthController::class, 'register');
$router->get('/logout', AuthController::class, 'logout');
// Task Routes
$router->get('/', TaskController::class, 'index');  // Home page (task list)
$router->get('/tasks/create', TaskController::class, 'create');
$router->post('/tasks/create', TaskController::class, 'create');
$router->get('/tasks/edit', TaskController::class, 'edit');  // Uses ?id=X query param
$router->post('/tasks/edit', TaskController::class, 'edit');
$router->get('/tasks/delete', TaskController::class, 'delete');
$router->get('/tasks/toggle', TaskController::class, 'toggle');  // Toggle completion status

// Dispatch the request
// This will call the appropriate controller method based on the URL
$router->dispatch();
