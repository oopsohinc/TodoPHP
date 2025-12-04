<?php 
$title = $title ?? 'Edit Task'; 
ob_start(); 
?>

<div class="form-wrapper">
    <div class="content-header">
        <div class="header-title">
            <h1><i class="fa-solid fa-pen-to-square"></i> Edit Task</h1>
        </div>
        <a href="/" class="btn-text"><i class="fa-solid fa-arrow-left"></i> Back to list</a>
    </div>

    <div class="form-card">
        <form method="POST" action="/tasks/edit?id=<?= $task['id'] ?>" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="title">Task Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
            </div>

            <?php if (!empty($task['image'])): ?>
                <div class="form-group">
                    <label>Current Attachment</label>
                    <div class="current-image-preview">
                        <img src="/uploads/<?= htmlspecialchars($task['image']) ?>" alt="Task Image">
                        <span class="text-muted"><i class="fa-regular fa-image"></i> <?= htmlspecialchars($task['image']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group half-width">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" value="<?= $task['due_date'] ?? '' ?>">
                </div>

                <div class="form-group half-width">
                    <label for="image">Change Image</label>
                    <input type="file" id="image" name="image" accept="image/*" class="form-control-file">
                    <small class="text-muted">Leave empty to keep current image</small>
                </div>
            </div>

            <div class="form-group">
                <div class="checkbox-wrapper">
                    <input type="checkbox" id="is_important" name="is_important" value="1" <?= !empty($task['is_important']) ? 'checked' : '' ?>>
                    <label for="is_important">Mark as Important <i class="fa-solid fa-star text-warning"></i></label>
                </div>
            </div>

            <div class="form-actions-right">
                <a href="/" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>