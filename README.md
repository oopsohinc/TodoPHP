# PHP MVC To-Do List Application <i class="fa-solid fa-check-double fa-beat"></i>

Một ứng dụng quản lý công việc (To-Do List) hiện đại, lấy cảm hứng từ Microsoft To-Do, được xây dựng **hoàn toàn bằng PHP thuần (Native PHP)** theo mô hình **MVC (Model-View-Controller)**.

Dự án thể hiện cách tổ chức code chuyên nghiệp, sạch sẽ, bảo mật, sử dụng Composer và kiến trúc MVC tự xây dựng mà **không phụ thuộc vào bất kỳ framework** nào.

---

## ✨ Tính năng nổi bật

### **1. Quản lý công việc (Tasks)**

* ✅ CRUD đầy đủ: Tạo, Xem, Sửa, Xóa công việc.
* 📁 Upload file hình ảnh cho từng task.
* 📅 *Due Date*: Hạn chót.
* ⭐ Đánh dấu quan trọng (*Important*).
* ☑️ Trạng thái hoàn thành/chưa hoàn thành.

### **2. Tổ chức thông minh (Smart Lists)**

* ☀️ **My Day**: Các công việc hôm nay.
* 🌟 **Important**: Công việc quan trọng.
* 📅 **Planned**: Việc có deadline.
* 📂 **Custom Lists**: Tạo danh sách tuỳ chỉnh.

### **3. Hệ thống tài khoản**

* 🔐 Đăng ký, Đăng nhập, Đăng xuất.
* 🛡️ Bảo mật:

  * Hash mật khẩu bằng **Bcrypt**.
  * Prepared Statements (**PDO**) chống SQLi.
  * Chống XSS cơ bản.

### **4. Giao diện (UI/UX)**

* 🎨 Giao diện 2 cột hiện đại (Sidebar + Content).
* 📱 Responsive trên Mobile.
* 🚀 HTML5/CSS3 thuần, không dùng framework nặng.

---

## 🛠️ Công nghệ sử dụng

* **PHP 8.0+**
* **MySQL** (PDO)
* **Composer** (PSR-4 Autoloading)
* **HTML5, CSS3 (Flexbox), JavaScript thuần**
* **Custom MVC Pattern**

---

## 📂 Cấu trúc thư mục (Project Structure)

```
project/
│
├── app/                  # Mã nguồn ứng dụng
│   ├── Controllers/      # Xử lý logic (TaskController, AuthController)
│   ├── Models/           # Tương tác DB (Task, User, TodoList)
│   ├── Views/            # Giao diện (HTML/PHP)
│   │   ├── auth/         # Views đăng nhập/đăng ký
│   │   ├── tasks/        # Views danh sách, tạo, sửa
│   │   └── layout.php    # Layout chính
│   └── Core/             # Router, Database, Session
│
├── config/               # Cấu hình hệ thống
├── public/               # Web root
│   ├── css/              # CSS
│   ├── uploads/          # Ảnh người dùng upload
|   ├── js/               # JS
│   └── index.php         # Entry point
│
├── vendor/               # Thư viện Composer
├── composer.json         # Cấu hình Composer
└── database.sql          # File tạo CSDL
```

---

## 🔄 Luồng hoạt động (Application Flow)

Luồng xử lý đơn giản và rõ ràng theo MVC:

1. **Request** → Người dùng truy cập URL (ví dụ: `/tasks/create`).
2. **Entry Point** → Tất cả request đi vào `public/index.php`.
3. **Router** → Phân tích URL, gọi đúng Controller và Action.
4. **Controller** →

   * (Tuỳ chọn) Kiểm tra đăng nhập.
   * Gọi Model để truy vấn/ghi dữ liệu.
   * Chuẩn bị dữ liệu và gửi sang View.
5. **View** →

   * Render giao diện với output buffering.
   * Nội dung view được nhúng vào `layout.php`.
6. **Response** → HTML hoàn chỉnh trả về trình duyệt.

---

## 🚀 Hướng dẫn cài đặt (Setup Guide)

### **Yêu cầu hệ thống**

* PHP >= 8.0
* MySQL
* Composer
* Apache/Nginx hoặc PHP Built-in Server

### **Bước 1: Clone dự án**

```bash
git clone https://github.com/your-username/your-repo.git
cd your-repo
```

### **Bước 2: Cài đặt Dependencies**

```bash
composer install
# Hoặc nếu chỉ cần autoload
composer dump-autoload
```

### **Bước 3: Cấu hình Database**

Tạo database mới (ví dụ `todo_app`).

Import file **database.sql**.

Cập nhật file `config/database.php`:

```php
return [
    'host'     => 'localhost',
    'dbname'   => 'todo_schema',
    'username' => 'root',
    'password' => '',
];
```

### **Bước 4: Chạy dự án**

```bash
php -S localhost:8000 -t public
```

### **Bước 5: Truy cập**

Mở trình duyệt:

```
http://localhost:8000
```

