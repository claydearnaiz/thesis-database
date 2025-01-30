<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Your Site Name</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="navbar">

    </div>

    <main class="main-content">
        <div class="container">
            <div class="auth-form">
                <h2>Create an Account</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="api/register.php" method="POST">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="form-control" placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               class="form-control" placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               class="form-control" placeholder="Create a password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               class="form-control" placeholder="Confirm your password">
                    </div>
                    
                    <div class="form-group">
                        <label for="user_type">Account Type</label>
                        <select id="user_type" name="user_type" class="form-control">
                            <option value="user">Regular User</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>
                
                <div class="auth-links">
                    <p>Already have an account? <a href="index.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>