<?php
session_start();
require_once 'config.php';

// CSRF token kontrolü
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token doğrulaması başarısız');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // SQL Injection koruması için prepared statement
    $query = "SELECT * FROM users WHERE username = :username AND password = :password";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'username' => $username,
        'password' => $password
    ]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        // Oturum güvenliği
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        // CSRF token yenileme
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        header('Location: dashboard.php');
        exit();
    }
    $error = "Geçersiz kullanıcı adı veya şifre!";
}

// CSRF token oluşturma
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Giriş Yap</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #007bff;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .form-control {
            border-radius: 5px;
            padding: 12px;
        }
        .btn-login {
            padding: 12px;
            border-radius: 5px;
            font-weight: bold;
        }
        .input-group-text {
            background: transparent;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-lock"></i> Giriş Yap</h3>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="form-group">
                            <label>Kullanıcı Adı</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" name="username" class="form-control" required maxlength="50" pattern="[a-zA-Z0-9_]+" title="Sadece harf, rakam ve alt çizgi kullanabilirsiniz">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Şifre</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-login">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 