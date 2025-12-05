<?php
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * TodoList Model
 * Quản lý các danh sách công việc tùy chỉnh (Custom Lists)
 */
class TodoList extends Model
{
    /**
     * Lấy tất cả danh sách của một user cụ thể
     * Sắp xếp theo ngày tạo tăng dần (cũ nhất lên trước)
     */
    public function getListsByUserId($userId)
    {
        $stmt = $this->getDb()->prepare("
            SELECT * FROM lists 
            WHERE user_id = :user_id 
            ORDER BY created_at ASC
        ");
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Tìm danh sách theo ID (kèm kiểm tra quyền sở hữu user_id)
     * Dùng để verify trước khi Sửa hoặc Xóa
     */
    public function findById($id, $userId)
    {
        $stmt = $this->getDb()->prepare("
            SELECT * FROM lists 
            WHERE id = :id AND user_id = :user_id 
            LIMIT 1
        ");
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Tạo danh sách mới
     */
    public function create($userId, $name)
    {
        $sql = "INSERT INTO lists (user_id, name, created_at) 
                VALUES (:user_id, :name, NOW())";

        $stmt = $this->getDb()->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId,
            ':name'    => $name
        ]);

        return $this->getDb()->lastInsertId();
    }

    /**
     * Cập nhật tên danh sách
     */
    public function update($id, $userId, $name)
    {
        $sql = "UPDATE lists 
                SET name = :name 
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->getDb()->prepare($sql);

        return $stmt->execute([
            ':name'    => $name,
            ':id'      => $id,
            ':user_id' => $userId
        ]);
    }

    /**
     * Xóa danh sách
     * Lưu ý: Các task trong list này sẽ bị set list_id = NULL hoặc bị xóa 
     * tùy theo cấu hình khóa ngoại (ON DELETE SET NULL/CASCADE) trong DB.
     */
    public function delete($id, $userId)
    {
        $stmt = $this->getDb()->prepare("
            DELETE FROM lists 
            WHERE id = :id AND user_id = :user_id
        ");

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}