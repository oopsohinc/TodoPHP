<?php
namespace App\Models;

use App\Core\Model;

class TodoList extends Model
{
    // Lấy tất cả danh sách tùy chỉnh của một user
    public function getListsByUserId($userId)
    {
        $sql = "SELECT * FROM lists WHERE user_id = :user_id ORDER BY created_at ASC";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // Tạo danh sách mới
    public function create($userId, $name)
    {
        $sql = "INSERT INTO lists (user_id, name) VALUES (:user_id, :name)";
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':name' => $name]);
    }
    
    // Các method update, delete list có thể thêm sau...
}