-- ================================================
-- MySQL Database Setup for To-Do MVC Application
-- ================================================

-- Tạo database (nếu chưa có) và chọn sử dụng
-- CREATE DATABASE IF NOT EXISTS todo_app;
-- USE todo_app;

-- ================================================
-- 1. Users Table
-- ================================================
-- Lưu thông tin tài khoản người dùng

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- 2. Tasks Table
-- ================================================
-- Lưu công việc (tasks). 
-- user_id liên kết với bảng users.

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    completed BOOLEAN DEFAULT FALSE, -- Trong MySQL sẽ lưu là 0 (False) hoặc 1 (True)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Tạo khóa ngoại (Foreign Key)
    CONSTRAINT fk_user_task 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- Indexes (Tùy chọn)
-- ================================================
-- MySQL tự động đánh index cho khóa ngoại (user_id)
-- nên lệnh dưới đây là dư thừa, nhưng vẫn chạy được.
CREATE INDEX idx_tasks_user_id ON tasks(user_id);

-- ================================================
-- Sample Data (Dữ liệu mẫu để test)
-- ================================================
-- Password bên dưới là 'password123' đã được hash

-- INSERT INTO users (name, email, password) 
-- VALUES ('Demo User', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- INSERT INTO tasks (user_id, title, description, completed) VALUES
-- (1, 'Học PHP MVC', 'Hiểu cách Model, View, Controller hoạt động.', 1),
-- (1, 'Làm ứng dụng To-Do', 'Thực hành thêm sửa xóa (CRUD) và Login.', 0),
-- (1, 'Làm chủ PDO Database', 'Học cách query an toàn chống SQL Injection.', 0);