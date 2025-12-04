<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login' ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>📝 To-Do MVC</h1>
            <h2>Login to Your Account</h2>

            <!-- Flash Messages -->
            <?php if ($success = \App\Core\Session::getFlash('success')): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error = \App\Core\Session::getFlash('error')): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="/login">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-link">
                Don't have an account? <a href="/register">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
