<?php
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * User Model
 * 
 * Handles all database operations related to users:
 * - Registration (creating new users)
 * - Login (verifying credentials)
 * - Finding users by email or ID
 * 
 * Security Features:
 * - Passwords are hashed using bcrypt (PASSWORD_DEFAULT)
 * - All queries use prepared statements to prevent SQL injection
 */
class User extends Model
{
    /**
     * Find a user by email
     * 
     * Used during login to fetch user data for password verification
     * Also used during registration to check if email already exists
     * 
     * @param string $email User's email address
     * @return array|false User data array or false if not found
     */
    public function findByEmail($email)
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Find a user by ID
     * 
     * Used to fetch user details from session user_id
     * 
     * @param int $id User's primary key ID
     * @return array|false User data array or false if not found
     */
    public function findById($id)
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Create a new user (Registration)
     * 
     * Password Hashing:
     * - Uses PASSWORD_DEFAULT (currently bcrypt)
     * - Automatically handles salt generation
     * - Future-proof: PHP will update the algorithm as needed
     * 
     * @param array $data User data with keys: name, email, password
     * @return bool True on success, false on failure
     */
    public function create($data)
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->getDb()->prepare("
            INSERT INTO users (name, email, password, created_at) 
            VALUES (:name, :email, :password, NOW())
        ");

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $hashedPassword);

        return $stmt->execute();
    }

    /**
     * Verify user credentials (Login)
     * 
     * How password verification works:
     * 1. Find user by email
     * 2. Use password_verify() to compare plain text with hash
     * 3. password_verify() handles all the bcrypt comparison securely
     * 
     * @param string $email User's email
     * @param string $password User's password (plain text from form)
     * @return array|false User data if valid, false if invalid
     */
    public function verify($email, $password)
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Check if email already exists
     * 
     * Used during registration to prevent duplicate accounts
     * 
     * @param string $email Email to check
     * @return bool True if email exists, false if available
     */
    public function emailExists($email)
    {
        return $this->findByEmail($email) !== false;
    }
}
