<?php
namespace App\Core;

/**
 * Base Controller Class
 * 
 * This is the parent class for all controllers (AuthController, TaskController, etc.)
 * It provides helper methods to load views and handle common tasks
 * 
 * Why use a base controller?
 * - DRY principle: Don't repeat code for loading views
 * - Consistent structure: All controllers work the same way
 */
class Controller
{
    /**
     * Load a view file and pass data to it
     * 
     * How it works:
     * 1. Extract $data array into variables
     * 2. Include the view file (which can now use those variables)
     * 
     * @param string $view Path to view file (e.g., 'tasks/index')
     * @param array $data Associative array of data to pass to view
     */
    protected function view($view, $data = [])
    {
        // Extract array keys as variable names
        // Example: ['title' => 'Home'] becomes $title = 'Home'
        extract($data);

        // Build the full path to the view file
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        // Check if view file exists
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View not found: {$view}");
        }
    }

    /**
     * Redirect to another URL
     * 
     * @param string $url URL to redirect to
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Check if user is logged in
     * Redirects to login if not authenticated
     */
    protected function requireAuth()
    {
        if (!Session::has('user_id')) {
            $this->redirect('/login');
        }
    }

    /**
     * Check if user is a guest (not logged in)
     * Redirects to home if already authenticated
     */
    protected function requireGuest()
    {
        if (Session::has('user_id')) {
            $this->redirect('/');
        }
    }
}
