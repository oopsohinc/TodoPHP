<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'To-Do MVC' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/alter_style.css">
    <link rel="stylesheet" href="../css/profile.css">
</head>

<body>

    <nav class="navbar">
        <div class="nav-brand">
            <i class="fa-solid fa-check-double"></i> To-Do
        </div>

        <?php if (\App\Core\Session::get('user_id')): ?>
            <div class="nav-user">
                <form class="search-box" action="/tasks/search" method="GET">
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <input type="text" name="q" placeholder="Search" required>
                </form>

                <div class="user-info">
                    <a href="/profile" class="user-profile-link" title="View Profile">
                        <span class="avatar"><?= strtoupper(substr(\App\Core\Session::get('user_name'), 0, 1)) ?></span>
                    </a>
                    <a href="/logout" class="btn-logout" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>
            </div>
        <?php endif; ?>
    </nav>

    <div class="app-container">

        <?php if (\App\Core\Session::get('user_id')): ?>
            <?php
            $currentFilter = $active_filter ?? ($preSelectedListId ?? 'inbox');
            ?>
            <aside class="sidebar">
                <div class="sidebar-group">
                    <a href="/tasks?filter=my-day" class="sidebar-item <?= $currentFilter === 'my-day' ? 'active' : '' ?>">
                        <div class="sidebar-item-content">
                            <div class="sidebar-item-label">
                                <i class="fa-solid fa-sun text-warning"></i> <span>My Day</span>
                            </div>
                            <span class="task-count"><?= $taskCounts['my-day'] ?? 0 ?></span>
                        </div>
                    </a>
                    <a href="/tasks?filter=important" class="sidebar-item <?= $currentFilter === 'important' ? 'active' : '' ?>">
                        <div class="sidebar-item-content">
                            <div class="sidebar-item-label">
                                <i class="fa-regular fa-star text-danger"></i> <span>Important</span>
                            </div>
                            <span class="task-count"><?= $taskCounts['important'] ?? 0 ?></span>
                        </div>
                    </a>
                    <a href="/tasks?filter=planned" class="sidebar-item <?= $currentFilter === 'planned' ? 'active' : '' ?>">
                        <div class="sidebar-item-content">
                            <div class="sidebar-item-label">
                                <i class="fa-solid fa-calendar-days text-info"></i> <span>Planned</span>
                            </div>
                            <span class="task-count"><?= $taskCounts['planned'] ?? 0 ?></span>
                        </div>
                    </a>
                    <a href="/tasks" class="sidebar-item <?= ($currentFilter === 'inbox' || $currentFilter === '') ? 'active' : '' ?>">
                        <div class="sidebar-item-content">
                            <div class="sidebar-item-label">
                                <i class="fa-solid fa-inbox text-primary"></i> <span>Tasks</span>
                            </div>
                            <span class="task-count"><?= $taskCounts['inbox'] ?? 0 ?></span>
                        </div>
                    </a>
                </div>

                <hr class="sidebar-divider">

                <div class="sidebar-group scrollable-group">
                    <?php if (!empty($userLists)): ?>
                        <?php foreach ($userLists as $list): ?>
                            <?php
                            // So sánh lỏng (==) để '5' (string) vẫn bằng 5 (int)
                            $isActive = ($currentFilter == $list['id']) ? 'active' : '';
                            $listCount = $taskCounts['lists'][$list['id']] ?? 0;
                            ?>
                            <a href="/tasks?list=<?= $list['id'] ?>" class="sidebar-item <?= $isActive ?>">
                                <div class="sidebar-item-content">
                                    <div class="sidebar-item-label">
                                        <i class="fa-solid fa-list-ul"></i> <span><?= htmlspecialchars($list['name']) ?></span>
                                    </div>
                                    <span class="task-count"><?= $listCount ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <a href="/lists/create" class="sidebar-item new-list-btn">
                    <i class="fa-solid fa-plus"></i> <span>New list</span>
                </a>
            </aside>
        <?php endif; ?>

        <main class="main-content">
            <div class="alerts-wrapper">
                <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
                    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $msg ?></div>
                <?php endif; ?>
                <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $msg ?></div>
                <?php endif; ?>
            </div>



            <?= $content ?? '' ?>

            <footer class="custom-footer">
                <div class="footer-divider">---------------------------------------------------------</div>
                <p>© 2026 Nhóm 13 – Khoa CNTT – Đại học Khoa Học Đại Học Huế</p>
                <p>Developed by: Nguyễn A, Trần B, Lê C</p>
                <p>
                    Liên hệ: <a href="https://github.com/oopsohinc/TodoPHP" target="_blank">https://github.com/oopsohinc/TodoPHP</a>
                </p>
                <div class="footer-divider">---------------------------------------------------------</div>
            </footer>
        </main>

                   
    </div>

    <script src="../js/main.js"></script>
</body>

</html>