<?php
ob_start();
?>

<header class="content-header">
    <div class="header-title">
        <h1><?= $title ?? 'Tasks' ?></h1>
        <p class="current-date"><?= date('l, F j') ?></p>
    </div>
    <div class="header-actions">
        <button class="btn-icon"><i class="fa-solid fa-arrow-down-short-wide"></i> Sort</button>
    </div>
</header>

<div class="task-list-container">

    <?php if (empty($tasks)): ?>
        <div class="empty-state-vertical">
            <i class="fa-solid fa-clipboard-check"></i>
            <p>All clear! You have no tasks here.</p>
        </div>
    <?php else: ?>

        <?php foreach ($tasks as $task): ?>
            <?php
            $isCompleted = !empty($task['completed']);
            $isImportant = !empty($task['is_important']);
            ?>

            <div class="task-item <?= $isCompleted ? 'completed' : '' ?>">

                <a href="/tasks/toggle?id=<?= $task['id'] ?>" class="task-checkbox-wrapper" title="Mark as completed">
                    <div class="custom-checkbox">
                        <?php if ($isCompleted): ?>
                            <i class="fa-solid fa-check"></i>
                        <?php endif; ?>
                    </div>
                </a>

                <a href="/tasks/edit?id=<?= $task['id'] ?>" class="task-content-link">
                    <div class="task-content">
                        <span class="task-title"><?= htmlspecialchars($task['title']) ?></span>

                        <div class="task-meta">
                            <span class="meta-list">Tasks</span>

                            <?php if (!empty($task['due_date'])): ?>
                                <span class="meta-separator">•</span>
                                <span class="meta-date <?= (strtotime($task['due_date']) < time() && !$isCompleted) ? 'text-danger' : '' ?>">
                                    <i class="fa-regular fa-calendar"></i> <?= date('M d', strtotime($task['due_date'])) ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($task['image'])): ?>
                                <span class="meta-separator">•</span>
                                <span class="meta-icon"><i class="fa-regular fa-image"></i> Image</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>

                <div class="task-actions-group">
                    <a href="/tasks/star?id=<?= $task['id'] ?>" class="action-btn star-btn <?= $isImportant ? 'active' : '' ?>">
                        <i class="<?= $isImportant ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                    </a>

                    <a href="/tasks/delete?id=<?= $task['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Delete this task?')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>

            </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <a href="/tasks/create" class="add-task-bar">
        <div class="add-icon">
            <i class="fa-solid fa-plus"></i>
        </div>
        <span>Add a task</span>
    </a>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>