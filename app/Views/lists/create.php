<?php 
$title = 'Create List';
ob_start(); 
?>

<div class="form-wrapper">
    <div class="content-header">
        <div class="header-title">
            <h1><i class="fa-solid fa-folder-plus"></i> Create New List</h1>
        </div>
        <a href="/" class="btn-text"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <div class="form-card">
        <form method="POST" action="/lists/create">
            <div class="form-group">
                <label for="name">List Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Shopping, Work..." required autofocus>
            </div>

            <div class="form-actions-right">
                <a href="/" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Create List
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>