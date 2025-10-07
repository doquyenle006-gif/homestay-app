<?php
// Include necessary files
include '../config/database.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Vui lòng đăng nhập để truy cập trang này!";
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user_info = getUserInfo($user_id);

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $current_password = sanitizeInput($_POST['current_password']);
    $new_password = sanitizeInput($_POST['new_password']);
    $confirm_password = sanitizeInput($_POST['confirm_password']);
    
    // Validate input
    $errors = [];
    
    if (empty($full_name) || empty($email)) {
        $errors[] = "Họ tên và email là bắt buộc!";
    }
    
    // Check if email already exists (excluding current user)
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors[] = "Email đã tồn tại!";
    }
    
    // Password change logic
    $password_changed = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Vui lòng nhập mật khẩu hiện tại để thay đổi mật khẩu!";
        } elseif ($current_password !== $user_info['password']) {
            $errors[] = "Mật khẩu hiện tại không chính xác!";
        } elseif (empty($new_password)) {
            $errors[] = "Vui lòng nhập mật khẩu mới!";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Mật khẩu mới phải có ít nhất 6 ký tự!";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "Mật khẩu xác nhận không khớp!";
        } else {
            $password_changed = true;
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        if ($password_changed) {
            $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $new_password, $user_id);
        } else {
            $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
        }
        
        if ($stmt->execute()) {
            // Update session data
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            
            $_SESSION['success_message'] = "Cập nhật thông tin thành công!";
            redirect('profile.php');
        } else {
            $errors[] = "Có lỗi xảy ra khi cập nhật thông tin. Vui lòng thử lại!";
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Hồ Sơ Cá Nhân</h2>
            <p class="text-muted">Quản lý thông tin cá nhân và tài khoản của bạn</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Thông tin cá nhân</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?php echo $user_info['username']; ?>" readonly>
                                    <div class="form-text">Tên đăng nhập không thể thay đổi.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Họ và tên *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : $user_info['full_name']; ?>" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? $_POST['email'] : $user_info['email']; ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : $user_info['phone']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                    <div class="form-text">Chỉ điền nếu muốn đổi mật khẩu.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Cập Nhật Thông Tin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin tài khoản</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Vai trò:</strong>
                        <p>
                            <?php 
                            if ($user_info['role'] === 'admin') {
                                echo '<span class="badge bg-danger">Quản trị viên</span>';
                            } else {
                                echo '<span class="badge bg-primary">Người dùng</span>';
                            }
                            ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Ngày tham gia:</strong>
                        <p><?php echo date('d/m/Y', strtotime($user_info['created_at'])); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Cập nhật lần cuối:</strong>
                        <p><?php echo date('d/m/Y H:i', strtotime($user_info['updated_at'])); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Thống kê đặt phòng:</strong>
                        <?php
                        $stats_sql = "SELECT status, COUNT(*) as count FROM bookings WHERE user_id = ? GROUP BY status";
                        $stats_stmt = $conn->prepare($stats_sql);
                        $stats_stmt->bind_param("i", $user_id);
                        $stats_stmt->execute();
                        $stats_result = $stats_stmt->get_result();
                        
                        while ($stat = $stats_result->fetch_assoc()) {
                            echo "<p>{$stat['status']}: {$stat['count']} đơn</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>