<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

/**
 * AuthController
 * 
 * Handles user authentication:
 * - Login
 * - Register
 * - Logout
 * 
 * MVC Flow Example (Login):
 * 1. User visits /login
 * 2. Router dispatches to AuthController->login()
 * 3. Controller loads View (login.php)
 * 4. User submits form
 * 5. Controller validates, uses Model to check database
 * 6. Controller redirects based on result
 */
class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        // Create User model instance
        $this->userModel = new User();
    }

    /**
     * Show login form
     */
    public function login()
    {
        // If already logged in, redirect to home
        $this->requireGuest();

        // Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }

        // Show login form (GET request)
        $this->view('auth/login', [
            'title' => 'Login'
        ]);
    }

    /**
     * Process login form submission
     */
    private function handleLogin()
    {
        // Get form data
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            Session::flash('error', 'Please fill in all fields');
            $this->redirect('/login');
            return;
        }

        // Verify credentials using User model
        $user = $this->userModel->verify($email, $password);

        if ($user) {
            // Login successful - create session
            Session::set('user_id', $user['id']);
            Session::set('user_name', $user['name']);
            Session::flash('success', 'Welcome back, ' . $user['name'] . '!');
            
            $this->redirect('/');
        } else {
            // Login failed
            Session::flash('error', 'Invalid email or password');
            $this->redirect('/login');
        }
    }

    /**
     * Show registration form
     */
    public function register()
    {
        // If already logged in, redirect to home
        $this->requireGuest();

        // Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
            return;
        }

        // Show registration form (GET request)
        $this->view('auth/register', [
            'title' => 'Register'
        ]);
    }

    /**
     * Process registration form submission
     */
    private function handleRegister()
    {
        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate input
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Name is required';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        // Check if email already exists
        if ($this->userModel->emailExists($email)) {
            $errors[] = 'Email already registered';
        }

        // If there are errors, redirect back
        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            $this->redirect('/register');
            return;
        }

        // Create user
        $result = $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        if ($result) {
            Session::flash('success', 'Registration successful! Please login.');
            $this->redirect('/login');
        } else {
            Session::flash('error', 'Registration failed. Please try again.');
            $this->redirect('/register');
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        Session::destroy();
        $this->redirect('/login');
    }
}
