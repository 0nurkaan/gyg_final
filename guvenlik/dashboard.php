<?php
session_start();
require_once 'config.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Oturum zaman aşımı kontrolü (30 dakika)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

// CSRF token kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token doğrulaması başarısız');
    }
}

$role = $_SESSION['role'] ?? 'guest';

// Güvenlik başlıkları
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:;");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Yönetim Paneli</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,.8);
        }
        .nav-link:hover {
            color: white;
        }
        .content {
            padding: 20px;
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3">
                    <h4>Yönetim Paneli</h4>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="#dashboard"><i class="fas fa-home"></i> Ana Sayfa</a>
                        </li>
                        <?php if ($role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#users"><i class="fas fa-users"></i> Kullanıcılar</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#logs"><i class="fas fa-history"></i> Sistem Logları</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#settings"><i class="fas fa-cog"></i> Ayarlar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                    <div class="user-info">
                        <span class="badge badge-primary"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
                    </div>
                </div>

                <div class="alert alert-info">
                    <?php echo htmlspecialchars($_GET['message'] ?? 'Hoş geldiniz!'); ?>
                </div>

                <!-- İstatistik Kartları -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card bg-primary text-white">
                            <h5>Toplam Kullanıcı</h5>
                            <h3 id="totalUsers">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-success text-white">
                            <h5>Aktif Kullanıcılar</h5>
                            <h3 id="activeUsers">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-warning text-white">
                            <h5>Bugünkü Girişler</h5>
                            <h3 id="todayLogins">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card bg-danger text-white">
                            <h5>Başarısız Girişler</h5>
                            <h3 id="failedLogins">0</h3>
                        </div>
                    </div>
                </div>

                <?php if ($role === 'admin'): ?>
                <!-- Kullanıcı Listesi -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Kullanıcı Listesi</h4>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-plus"></i> Yeni Kullanıcı
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="userList">
                            <!-- AJAX ile kullanıcı listesi yüklenecek -->
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
    <!-- Yeni Kullanıcı Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Kullanıcı Ekle</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="form-group">
                            <label>Kullanıcı Adı</label>
                            <input type="text" class="form-control" name="username" required maxlength="50" pattern="[a-zA-Z0-9_]+" title="Sadece harf, rakam ve alt çizgi kullanabilirsiniz">
                        </div>
                        <div class="form-group">
                            <label>Şifre</label>
                            <input type="password" class="form-control" name="password" required minlength="8">
                        </div>
                        <div class="form-group">
                            <label>Rol</label>
                            <select class="form-control" name="role" required>
                                <option value="user">Kullanıcı</option>
                                <option value="editor">Editör</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" id="saveUser">Kaydet</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // AJAX ile kullanıcı listesini yükle
        function loadUsers() {
            $.ajax({
                url: 'get_users.php',
                method: 'GET',
                headers: {
                    'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                },
                success: function(response) {
                    $('#userList').html(response);
                }
            });
        }

        // Sayfa yüklendiğinde kullanıcıları getir
        <?php if ($role === 'admin'): ?>
        loadUsers();
        <?php endif; ?>

        // Yeni kullanıcı kaydetme
        $('#saveUser').click(function() {
            var formData = $('#addUserForm').serialize();
            $.ajax({
                url: 'add_user.php',
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#addUserModal').modal('hide');
                    loadUsers();
                }
            });
        });

        // Kullanıcı silme
        $(document).on('click', '.delete-user', function() {
            if(confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')) {
                var userId = $(this).data('id');
                $.ajax({
                    url: 'delete_user.php',
                    method: 'POST',
                    data: {
                        id: userId,
                        csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    success: function(response) {
                        loadUsers();
                    }
                });
            }
        });
    });
    </script>
</body>
</html> 