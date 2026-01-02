<?php
$title = $title ?? 'My Account';
ob_start();
?>

<div class="profile-wrapper">
    <div class="profile-header">
        <div class="profile-avatar-section">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h1 class="profile-name"><?= htmlspecialchars($user['name']) ?></h1>
                <p class="profile-email">
                    <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?>
                </p>
                <p class="profile-join-date">
                    <i class="fa-solid fa-calendar"></i> Joined <?= date('F j, Y', strtotime($user['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <!-- Statistics Section -->
        <div class="stats-section">
            <h2 class="section-title">
                <i class="fa-solid fa-chart-simple"></i> Task Statistics
            </h2>

            <div class="stats-grid">
                <!-- Total Tasks -->
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fa-solid fa-tasks"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?= $stats['total'] ?></h3>
                        <p class="stat-label">Total Tasks</p>
                    </div>
                </div>

                <!-- Completed Tasks -->
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?= $stats['completed'] ?></h3>
                        <p class="stat-label">Completed</p>
                    </div>
                </div>

                <!-- Incomplete Tasks -->
                <div class="stat-card">
                    <div class="stat-icon incomplete">
                        <i class="fa-solid fa-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?= $stats['incomplete'] ?></h3>
                        <p class="stat-label">Incomplete</p>
                    </div>
                </div>

                <!-- Important Tasks -->
                <div class="stat-card">
                    <div class="stat-icon important">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?= $stats['important'] ?></h3>
                        <p class="stat-label">Important</p>
                    </div>
                </div>

                <!-- Completion Rate -->
                <div class="stat-card">
                    <div class="stat-icon rate">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?= $stats['completion_rate'] ?>%</h3>
                        <p class="stat-label">Completion Rate</p>
                    </div>
                </div>

                <!-- Total Lists -->
                <div class="stat-card">
                    <div class="stat-icon lists">
                        <i class="fa-solid fa-list"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?= count($userLists) ?></h3>
                        <p class="stat-label">Custom Lists</p>
                    </div>
                </div>
            </div>

            <!-- Completion Progress Bar -->
            <?php if ($stats['total'] > 0): ?>
                <div class="progress-section">
                    <div class="progress-header">
                        <span class="progress-label">Overall Progress</span>
                        <span class="progress-percentage"><?= $stats['completion_rate'] ?>%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= $stats['completion_rate'] ?>%"></div>
                    </div>
                    <div class="progress-info">
                        <span><?= $stats['completed'] ?> of <?= $stats['total'] ?> tasks completed</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state-note">
                    <p><i class="fa-solid fa-info-circle"></i> No tasks yet. <a href="/tasks/create">Create your first task!</a></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lists Section -->
        <?php if (!empty($userLists)): ?>
            <div class="lists-section">
                <h2 class="section-title">
                    <i class="fa-solid fa-layer-group"></i> My Lists (<?= count($userLists) ?>)
                </h2>

                <div class="lists-grid">
                    <?php foreach ($userLists as $list): ?>
                        <?php $listCount = $taskCounts['lists'][$list['id']] ?? 0; ?>
                        <div class="list-card">
                            <div class="list-header">
                                <h3 class="list-name"><?= htmlspecialchars($list['name']) ?></h3>
                                <span class="list-task-count"><?= $listCount ?></span>
                            </div>
                            <p class="list-created">Created <?= date('M d, Y', strtotime($list['created_at'])) ?></p>
                            <div class="list-actions">
                                <a href="/tasks?list=<?= $list['id'] ?>" class="btn-link small">View Tasks</a>
                                <a href="/lists/edit?id=<?= $list['id'] ?>" class="btn-link small">Edit</a>
                                <a href="/lists/delete?id=<?= $list['id'] ?>" class="btn-link small text-danger" onclick="return confirm('Delete this list?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
