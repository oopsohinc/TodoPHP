<?php 
$title = 'Edit List';
ob_start(); 
?>

<div class="form-wrapper">
    <div class="content-header">
        <div class="header-title">
            <h1><i class="fa-solid fa-pen-to-square"></i> Edit List</h1>
        </div>
        <a href="/tasks?list=<?= $list['id'] ?>" class="btn-text"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <div class="form-card">
        <form method="POST" action="/lists/edit?id=<?= $list['id'] ?>">
            <div class="form-group">
                <label for="name">List Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($list['name']) ?>" required>
            </div>

            <div class="form-actions-right">
                <a href="/tasks?list=<?= $list['id'] ?>" class="btn btn-secondary">Cancel</a>
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