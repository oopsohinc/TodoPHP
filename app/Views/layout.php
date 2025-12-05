<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'To-Do MVC' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/alter_style.css">
</head>

<body>

    <nav class="navbar">
        <div class="nav-brand">
            <i class="fa-solid fa-check-double"></i> To-Do
        </div>

        <?php if (\App\Core\Session::get('user_id')): ?>
            <div class="nav-user">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search">
                </div>

                <div class="user-info">
                    <span class="avatar"><?= strtoupper(substr(\App\Core\Session::get('user_name'), 0, 1)) ?></span>
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
                        <i class="fa-solid fa-sun text-warning"></i> <span>My Day</span>
                    </a>
                    <a href="/tasks?filter=important" class="sidebar-item <?= $currentFilter === 'important' ? 'active' : '' ?>">
                        <i class="fa-regular fa-star text-danger"></i> <span>Important</span>
                    </a>
                    <a href="/tasks?filter=planned" class="sidebar-item <?= $currentFilter === 'planned' ? 'active' : '' ?>">
                        <i class="fa-solid fa-calendar-days text-info"></i> <span>Planned</span>
                    </a>
                    <a href="/tasks" class="sidebar-item <?= ($currentFilter === 'inbox' || $currentFilter === '') ? 'active' : '' ?>">
                        <i class="fa-solid fa-inbox text-primary"></i> <span>Tasks</span>
                    </a>
                </div>

                <hr class="sidebar-divider">

                <div class="sidebar-group scrollable-group">
                    <?php if (!empty($userLists)): ?>
                        <?php foreach ($userLists as $list): ?>
                            <?php
                            // So sánh lỏng (==) để '5' (string) vẫn bằng 5 (int)
                            $isActive = ($currentFilter == $list['id']) ? 'active' : '';
                            ?>
                            <a href="/tasks?list=<?= $list['id'] ?>" class="sidebar-item <?= $isActive ?>">
                                <i class="fa-solid fa-list-ul"></i> <span><?= htmlspecialchars($list['name']) ?></span>
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
        </main>
    </div>

    <script src="../js/main.js"></script>
</body>

</html>