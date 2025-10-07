<?php
// Include necessary files
include '../config/database.php';
include '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    $_SESSION['error_message'] = "Bạn không có quyền truy cập trang quản trị!";
    redirect('../index.php');
}

// Process actions
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : '';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Delete user
if ($action == 'delete' && $user_id > 0) {
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Bạn không thể xóa tài khoản của chính mình!";
    } else {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Xóa người dùng thành công!";
        } else {
            $_SESSION['error_message'] = "Có lỗi xảy ra khi xóa người dùng!";
        }
    }
    redirect('users.php');
}

// Update user role
if ($action == 'update_role' && $user_id > 0) {
    $new_role = isset($_GET['role']) ? sanitizeInput($_GET['role']) : '';
    $valid_roles = ['admin', 'user'];
    
    if (in_array($new_role, $valid_roles)) {
        // Prevent admin from changing their own role
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error_message'] = "Bạn không thể thay đổi vai trò của chính mình!";
        } else {
            $sql = "UPDATE users SET role = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_role, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Cập nhật vai trò người dùng thành công!";
            } else {
                $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật vai trò!";
            }
        }
    } else {
        $_SESSION['error_message'] = "Vai trò không hợp lệ!";
    }
    redirect('users.php');
}

// Get filter parameters
$role_filter = isset($_GET['role']) ? sanitizeInput($_GET['role']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build SQL query with filters
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$sql .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users_result = $stmt->get_result();

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Quản Lý Người Dùng</h2>
            <p class="text-muted">Quản lý tất cả người dùng trong hệ thống</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="role" class="form-label">Vai trò</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">Tất cả vai trò</option>
                                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                    <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Người dùng</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label for="search" class="form-label">Tìm kiếm</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo $search; ?>" placeholder="Tên đăng nhập, email, họ tên...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Lọc
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($role_filter) || !empty($search)): ?>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="users.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-refresh"></i> Xóa bộ lọc
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Danh sách người dùng</h5>
                </div>
                <div class="card-body">
                    <?php if ($users_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Thông tin</th>
                                        <th>Liên hệ</th>
                                        <th>Vai trò</th>
                                        <th>Ngày tham gia</th>
                                        <th>Thống kê</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users_result->fetch_assoc()): 
                                        // Get user statistics
                                        $bookings_sql = "SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?";
                                        $bookings_stmt = $conn->prepare($bookings_sql);
                                        $bookings_stmt->bind_param("i", $user['id']);
                                        $bookings_stmt->execute();
                                        $bookings_result = $bookings_stmt->get_result();
                                        $bookings_count = $bookings_result->fetch_assoc()['total_bookings'];
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $user['full_name']; ?></strong>
                                                <br>
                                                <small class="text-muted">@<?php echo $user['username']; ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo $user['email']; ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo $user['phone'] ?: 'Chưa cập nhật'; ?></small>
                                            </td>
                                            <td>
                                                <?php if ($user['role'] == 'admin'): ?>
                                                    <span class="badge bg-danger">Quản trị viên</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">Người dùng</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($user['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <small>Đặt phòng: <strong><?php echo $bookings_count; ?></strong></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <!-- Role update buttons -->
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <?php if ($user['role'] == 'user'): ?>
                                                            <a href="users.php?action=update_role&id=<?php echo $user['id']; ?>&role=admin" 
                                                               class="btn btn-outline-success" 
                                                               title="Thăng làm quản trị viên"
                                                               onclick="return confirm('Bạn có chắc chắn muốn thăng người dùng này làm quản trị viên?')">
                                                                <i class="fas fa-user-shield"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="users.php?action=update_role&id=<?php echo $user['id']; ?>&role=user" 
                                                               class="btn btn-outline-warning" 
                                                               title="Hạ xuống người dùng"
                                                               onclick="return confirm('Bạn có chắc chắn muốn hạ người dùng này xuống vai trò người dùng?')">
                                                                <i class="fas fa-user"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Delete button -->
                                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                                           class="btn btn-outline-danger" 
                                                           title="Xóa người dùng"
                                                           onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này? Hành động này không thể hoàn tác!')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Tài khoản của bạn</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Không tìm thấy người dùng nào phù hợp với tiêu chí tìm kiếm.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Thống kê người dùng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $total_users = executeQuery("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                        $admin_users = executeQuery("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
                        $regular_users = executeQuery("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
                        $active_users = executeQuery("SELECT COUNT(DISTINCT user_id) as count FROM bookings")->fetch_assoc()['count'];
                        ?>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $total_users; ?></h4>
                                    <p class="mb-0">Tổng người dùng</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $admin_users; ?></h4>
                                    <p class="mb-0">Quản trị viên</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $regular_users; ?></h4>
                                    <p class="mb-0">Người dùng thường</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $active_users; ?></h4>
                                    <p class="mb-0">Đã đặt phòng</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>