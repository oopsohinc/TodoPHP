<?php
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Task Model
 * 
 * Handles all database operations for tasks (to-do items):
 * - CRUD operations: Create, Read, Update, Delete
 * - Toggle completion status
 * - User-specific queries (each user sees only their tasks)
 * 
 * Security:
 * - All queries include user_id check to prevent unauthorized access
 * - Prepared statements prevent SQL injection
 */
class Task extends Model
{
    /**
     * Get all tasks for a specific user
     * 
     * Results are ordered by creation date (newest first)
     * 
     * @param int $userId User's ID from session
     * @return array Array of task records
     */
    public function getAllByUser($userId)
    {
        $stmt = $this->getDb()->prepare("
            SELECT * FROM tasks 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC
        ");
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Lấy task của user, có thể lọc theo list_id
    public function getTasksByUserId($userId, $filter = 'inbox')
    {
        $sql = "SELECT * FROM tasks WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        // 1. Lọc theo Custom List ID (VD: ?list=5)
        if (is_numeric($filter) && (int)$filter > 0) {
            $sql .= " AND list_id = :list_id";
            $params[':list_id'] = $filter;
        }
        // 2. Lọc theo trạng thái đặc biệt
        elseif ($filter === 'important') {
            $sql .= " AND is_important = 1";
        }
        elseif ($filter === 'my-day') {
            $sql .= " AND due_date = CURDATE()";
        }
        elseif ($filter === 'planned') {
            $sql .= " AND due_date IS NOT NULL ORDER BY due_date ASC";
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }
        // 3. Mặc định (Inbox) - Xử lý cả NULL và 0
        else {
            // Hiển thị task không thuộc list nào (Inbox)
            // Sửa lỗi: Một số DB lưu 0 thay vì NULL, nên ta check cả 2
            $sql .= " AND (list_id IS NULL OR list_id = 0)";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a task by ID (with user ownership check)
     * 
     * Important: Always includes user_id in WHERE clause
     * This prevents users from accessing other users' tasks
     * 
     * @param int $id Task ID
     * @param int $userId User ID (for security)
     * @return array|false Task data or false if not found/unauthorized
     */
    public function findById($id, $userId)
    {
        $stmt = $this->getDb()->prepare("
            SELECT * FROM tasks 
            WHERE id = :id AND user_id = :user_id 
            LIMIT 1
        ");
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Create a new task
     * 
     * New tasks start with completed = false
     * 
     * @param array $data Task data (user_id, title, description)
     * @return int|false Last inserted ID on success, false on failure
     */
    public function create($data)
    {
        $sql = "INSERT INTO tasks (user_id, list_id, title, description, is_important, due_date, completed, created_at) 
                VALUES (:user_id, :list_id, :title, :description, :is_important, :due_date, 0, NOW())";

        $stmt = $this->getDb()->prepare($sql);

        $stmt->execute([
            ':user_id'      => $data['user_id'],
            ':list_id'      => $data['list_id'] ?? null,      // Nếu không có thì là NULL
            ':title'        => $data['title'],
            ':description'  => $data['description'] ?? '',
            ':is_important' => $data['is_important'] ?? 0,    // Mặc định false
            ':due_date'     => $data['due_date'] ?? null      // YYYY-MM-DD hoặc NULL
        ]);

        return $this->getDb()->lastInsertId();
    }

    /**
     * Update an existing task
     * 
     * Updates title, description, list, importance, and due date
     * Completion status is handled separately by toggleComplete()
     * 
     * @param int $id Task ID
     * @param array $data Updated task data
     * @param int $userId User ID (for security check)
     * @return bool True on success
     */
    public function update($id, $data, $userId)
    {
        $sql = "UPDATE tasks 
                SET title = :title, 
                    description = :description, 
                    list_id = :list_id,
                    is_important = :is_important,
                    due_date = :due_date
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->getDb()->prepare($sql);

        return $stmt->execute([
            ':title'        => $data['title'],
            ':description'  => $data['description'] ?? '',
            ':list_id'      => $data['list_id'] ?? null,
            ':is_important' => $data['is_important'] ?? 0,
            ':due_date'     => $data['due_date'] ?? null,
            ':id'           => $id,
            ':user_id'      => $userId
        ]);
    }

    /**
     * Delete a task
     * 
     * Permanently removes the task from the database
     * 
     * @param int $id Task ID
     * @param int $userId User ID (for security check)
     * @return bool True on success
     */
    public function delete($id, $userId)
    {
        $stmt = $this->getDb()->prepare("
            DELETE FROM tasks 
            WHERE id = :id AND user_id = :user_id
        ");

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Toggle task completion status
     * 
     * How it works:
     * - If completed = true, set to false
     * - If completed = false, set to true
     * 
     * Uses PostgreSQL's NOT operator to flip the boolean
     * 
     * @param int $id Task ID
     * @param int $userId User ID (for security check)
     * @return bool True on success
     */
    public function toggleComplete($id, $userId)
    {
        $stmt = $this->getDb()->prepare("
            UPDATE tasks 
            SET completed = NOT completed 
            WHERE id = :id AND user_id = :user_id
        ");

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Đảo trạng thái quan trọng (Ngôi sao)
    public function toggleImportant($id, $userId)
    {
        $sql = "UPDATE tasks SET is_important = NOT is_important WHERE id = :id AND user_id = :user_id";
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Count tasks by filter for a specific user
     * 
     * @param int $userId User ID
     * @param string $filter Filter type (inbox, important, my-day, planned, or list_id)
     * @return int Number of tasks matching the filter
     */
    public function countByFilter($userId, $filter = 'inbox')
    {
        $sql = "SELECT COUNT(*) as total FROM tasks WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if (is_numeric($filter) && (int)$filter > 0) {
            $sql .= " AND list_id = :list_id";
            $params[':list_id'] = $filter;
        } elseif ($filter === 'important') {
            $sql .= " AND is_important = 1";
        } elseif ($filter === 'my-day') {
            $sql .= " AND due_date = CURDATE()";
        } elseif ($filter === 'planned') {
            $sql .= " AND due_date IS NOT NULL";
        } else {
            $sql .= " AND (list_id IS NULL OR list_id = 0)";
        }

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Get count of tasks for all special filters and lists for a user
     * 
     * @param int $userId User ID
     * @param array $userLists Array of user's custom lists
     * @return array Associative array with counts for each filter/list
     */
    public function getTaskCounts($userId, $userLists = [])
    {
        $counts = [
            'inbox' => $this->countByFilter($userId, 'inbox'),
            'my-day' => $this->countByFilter($userId, 'my-day'),
            'important' => $this->countByFilter($userId, 'important'),
            'planned' => $this->countByFilter($userId, 'planned'),
            'lists' => []
        ];

        foreach ($userLists as $list) {
            $counts['lists'][$list['id']] = $this->countByFilter($userId, $list['id']);
        }

        return $counts;
    }

    /**
     * Get comprehensive task statistics for a user
     * 
     * @param int $userId User ID
     * @return array Statistics including total, completed, incomplete, important counts
     */
    public function getStatistics($userId)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN completed = 0 THEN 1 ELSE 0 END) as incomplete,
                    SUM(CASE WHEN is_important = 1 THEN 1 ELSE 0 END) as important,
                    MIN(created_at) as first_task_date,
                    MAX(created_at) as last_task_date
                FROM tasks 
                WHERE user_id = :user_id";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();

        return [
            'total' => (int)$result['total'],
            'completed' => (int)($result['completed'] ?? 0),
            'incomplete' => (int)($result['incomplete'] ?? 0),
            'important' => (int)($result['important'] ?? 0),
            'completion_rate' => $result['total'] > 0 ? round(((int)$result['completed'] / (int)$result['total']) * 100, 1) : 0,
            'first_task_date' => $result['first_task_date'],
            'last_task_date' => $result['last_task_date']
        ];
    }

    /**
     * Search tasks by title or description
     * 
     * @param int $userId User's ID
     * @param string $query Search query
     * @return array Array of matching tasks
     */
    public function searchTasks($userId, $query)
    {
        $stmt = $this->getDb()->prepare("
            SELECT * FROM tasks 
            WHERE user_id = ? 
            AND (title LIKE ? OR description LIKE ?)
            ORDER BY created_at DESC
        ");
        
        $searchTerm = '%' . $query . '%';
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->bindParam(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindParam(3, $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
