<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Register' ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>📝 To-Do MVC</h1>
            <h2>Create New Account</h2>

            <!-- Flash Messages -->
            <?php if ($error = \App\Core\Session::getFlash('error')): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="/register">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small>Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <p class="auth-link">
                Already have an account? <a href="/login">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
