<?php
namespace App\Core;

/**
 * Session Management Class
 * 
 * Handles all session operations (start, set, get, destroy)
 * 
 * Why a Session class?
 * - Cleaner code: Session::get('user_id') is more readable than $_SESSION['user_id']
 * - Safety: Automatically starts session and prevents errors
 * - Centralized: All session logic in one place
 */
class Session
{
    /**
     * Start the session if not already started
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set a session variable
     * 
     * @param string $key Session key
     * @param mixed $value Value to store
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session variable exists
     * 
     * @param string $key Session key
     * @return bool
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session variable
     * 
     * @param string $key Session key
     */
    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the entire session
     * Used for logout
     */
    public static function destroy()
    {
        self::start();
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Set a flash message (one-time message)
     * 
     * @param string $key Flash message key
     * @param string $message Message content
     */
    public static function flash($key, $message)
    {
        self::set('flash_' . $key, $message);
    }

    /**
     * Get and remove a flash message
     * 
     * @param string $key Flash message key
     * @return string|null
     */
    public static function getFlash($key)
    {
        $message = self::get('flash_' . $key);
        self::remove('flash_' . $key);
        return $message;
    }
}
