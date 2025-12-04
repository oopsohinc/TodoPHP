<?php
namespace App\Core;

/**
 * Router Class
 * 
 * Handles URL routing and dispatches requests to appropriate controllers
 * 
 * How routing works:
 * 1. Parse the URL (e.g., /tasks/create)
 * 2. Determine controller (TaskController) and method (create)
 * 3. Call the method on the controller
 * 
 * Why use a router?
 * - Clean URLs: /tasks instead of index.php?page=tasks
 * - MVC pattern: URLs map to Controller actions
 */
class Router
{
    private $routes = [];

    /**
     * Add a GET route
     * 
     * @param string $path URL path
     * @param string $controller Controller class name
     * @param string $method Method to call on controller
     */
    public function get($path, $controller, $method)
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Add a POST route
     * 
     * @param string $path URL path
     * @param string $controller Controller class name
     * @param string $method Method to call on controller
     */
    public function post($path, $controller, $method)
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Dispatch the request to the appropriate controller
     * 
     * This is the heart of the router:
     * 1. Get the current URL and request method
     * 2. Find matching route
     * 3. Instantiate controller and call method
     */
    public function dispatch()
    {
        // Get request method (GET or POST)
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Get the URI path (e.g., /tasks/create)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // --- DEBUG ---
        // echo "Phuong thuc: " . $requestMethod . "<br>";
        // echo "Duong dan (URI): [" . $uri . "]<br>"; 
        // echo "Cac route da khai bao:<pre>";
        // print_r($this->routes);
        // echo "</pre>";
        // -------------------------------

        // Check if route exists for this method and path
        if (isset($this->routes[$requestMethod][$uri])) {
            $route = $this->routes[$requestMethod][$uri];
            
            $controllerName = $route['controller'];
            $methodName = $route['method'];

            // Create controller instance
            $controller = new $controllerName();
            
            // Call the method
            $controller->$methodName();
            
        } else {
            // Route not found - show 404
            http_response_code(404);
            echo "<h1>404 - Page Not Found</h1>";
            echo "<p>The page you're looking for doesn't exist.</p>";
            echo "<a href='/'>Go to Home</a>";
        }
    }
}
