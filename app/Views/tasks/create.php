<?php 
// 1. Khai báo tiêu đề (nếu chưa có trong $data)
$title = $title ?? 'Create Task'; 

// 2. Bắt đầu bộ nhớ đệm
ob_start(); 
?>

<div class="form-wrapper">
    <div class="content-header">
        <div class="header-title">
            <h1><i class="fa-solid fa-plus-circle"></i> Create New Task</h1>
        </div>
        <a href="/" class="btn-text"><i class="fa-solid fa-arrow-left"></i> Back to list</a>
    </div>

    <div class="form-card">
        <form method="POST" action="/tasks/create" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Task Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" placeholder="What needs to be done?" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Add details..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group half-width">
                    <label for="due_date">Due Date (Optional)</label>
                    <input type="date" id="due_date" name="due_date" class="form-control">
                </div>

                <div class="form-group half-width">
                    <label for="image">Attachment</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="image" name="image" accept="image/*" class="form-control-file">
                        <small class="text-muted">Max 5MB (JPG, PNG)</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                 <div class="checkbox-wrapper">
                    <input type="checkbox" id="is_important" name="is_important" value="1">
                    <label for="is_important">Mark as Important <i class="fa-solid fa-star text-warning"></i></label>
                </div>
            </div>

            <div class="form-actions-right">
                <a href="/" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Create Task
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
// 3. Lấy nội dung HTML ở trên gán vào biến $content
$content = ob_get_clean(); 

// 4. Gọi Layout chính để hiển thị (Layout sẽ in biến $content ra)
// Lưu ý đường dẫn: file này đang ở app/Views/tasks/ nên phải ra ngoài 1 cấp để thấy layout.php
include __DIR__ . '/../layout.php'; 
?>