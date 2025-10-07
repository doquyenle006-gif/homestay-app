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
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Update booking status
if ($action == 'update_status' && $booking_id > 0) {
    $new_status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
    $valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    
    if (in_array($new_status, $valid_statuses)) {
        $sql = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $booking_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Cập nhật trạng thái đặt phòng thành công!";
        } else {
            $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật trạng thái!";
        }
    } else {
        $_SESSION['error_message'] = "Trạng thái không hợp lệ!";
    }
    redirect('bookings.php');
}

// Delete booking
if ($action == 'delete' && $booking_id > 0) {
    $sql = "DELETE FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Xóa đặt phòng thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi xóa đặt phòng!";
    }
    redirect('bookings.php');
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build SQL query with filters
$sql = "SELECT b.*, u.full_name, u.email, u.phone, r.name as room_name, r.price_per_night 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN rooms r ON b.room_id = r.id 
        WHERE 1=1";
$params = [];
$types = "";

if (!empty($status_filter)) {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR r.name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$sql .= " ORDER BY b.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bookings_result = $stmt->get_result();

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Quản Lý Đặt Phòng</h2>
            <p class="text-muted">Xem và quản lý tất cả đặt phòng trong hệ thống</p>
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
                            <div class="col-md-4">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Đang chờ</option>
                                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Đã hoàn thành</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label">Tìm kiếm</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo $search; ?>" placeholder="Tên khách hàng, email, tên phòng...">
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
                        <?php if (!empty($status_filter) || !empty($search)): ?>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="bookings.php" class="btn btn-outline-secondary btn-sm">
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

    <!-- Bookings List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách đặt phòng</h5>
                </div>
                <div class="card-body">
                    <?php if ($bookings_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mã đặt</th>
                                        <th>Khách hàng</th>
                                        <th>Phòng</th>
                                        <th>Ngày nhận/trả</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <strong><?php echo $booking['full_name']; ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $booking['email']; ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo $booking['phone']; ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo $booking['room_name']; ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo formatPrice($booking['price_per_night']); ?>/đêm</small>
                                            </td>
                                            <td>
                                                <strong><?php echo date('d/m/Y', strtotime($booking['check_in_date'])); ?></strong>
                                                <br>
                                                <small class="text-muted">đến</small>
                                                <br>
                                                <strong><?php echo date('d/m/Y', strtotime($booking['check_out_date'])); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo calculateNights($booking['check_in_date'], $booking['check_out_date']); ?> đêm
                                                </small>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?php echo formatPrice($booking['total_price']); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch ($booking['status']) {
                                                    case 'confirmed':
                                                        $status_class = 'badge bg-success';
                                                        $status_text = 'Đã xác nhận';
                                                        break;
                                                    case 'pending':
                                                        $status_class = 'badge bg-warning';
                                                        $status_text = 'Đang chờ';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'badge bg-danger';
                                                        $status_text = 'Đã hủy';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'badge bg-info';
                                                        $status_text = 'Đã hoàn thành';
                                                        break;
                                                    default:
                                                        $status_class = 'badge bg-secondary';
                                                        $status_text = $booking['status'];
                                                }
                                                ?>
                                                <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <!-- Status update buttons -->
                                                    <?php if ($booking['status'] != 'confirmed'): ?>
                                                        <a href="bookings.php?action=update_status&id=<?php echo $booking['id']; ?>&status=confirmed" 
                                                           class="btn btn-outline-success" 
                                                           title="Xác nhận đặt phòng">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($booking['status'] != 'cancelled'): ?>
                                                        <a href="bookings.php?action=update_status&id=<?php echo $booking['id']; ?>&status=cancelled" 
                                                           class="btn btn-outline-danger" 
                                                           title="Hủy đặt phòng"
                                                           onclick="return confirm('Bạn có chắc chắn muốn hủy đặt phòng này?')">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                                        <a href="bookings.php?action=update_status&id=<?php echo $booking['id']; ?>&status=completed" 
                                                           class="btn btn-outline-info" 
                                                           title="Đánh dấu hoàn thành">
                                                            <i class="fas fa-flag-checkered"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Delete button -->
                                                    <a href="bookings.php?action=delete&id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-outline-dark" 
                                                       title="Xóa đặt phòng"
                                                       onclick="return confirm('Bạn có chắc chắn muốn xóa đặt phòng này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Không tìm thấy đặt phòng nào phù hợp với tiêu chí tìm kiếm.</p>
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
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê đặt phòng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $stats_sql = "SELECT status, COUNT(*) as count, SUM(total_price) as revenue 
                                     FROM bookings 
                                     GROUP BY status";
                        $stats_result = executeQuery($stats_sql);
                        
                        while ($stat = $stats_result->fetch_assoc()):
                            $bg_class = '';
                            switch ($stat['status']) {
                                case 'confirmed': $bg_class = 'bg-success'; break;
                                case 'pending': $bg_class = 'bg-warning'; break;
                                case 'cancelled': $bg_class = 'bg-danger'; break;
                                case 'completed': $bg_class = 'bg-info'; break;
                                default: $bg_class = 'bg-secondary';
                            }
                        ?>
                            <div class="col-md-3 mb-3">
                                <div class="card <?php echo $bg_class; ?> text-white">
                                    <div class="card-body text-center">
                                        <h5><?php echo $stat['count']; ?></h5>
                                        <p class="mb-1">
                                            <?php 
                                            switch ($stat['status']) {
                                                case 'confirmed': echo 'Đã xác nhận'; break;
                                                case 'pending': echo 'Đang chờ'; break;
                                                case 'cancelled': echo 'Đã hủy'; break;
                                                case 'completed': echo 'Đã hoàn thành'; break;
                                                default: echo $stat['status'];
                                            }
                                            ?>
                                        </p>
                                        <?php if ($stat['revenue']): ?>
                                            <small><?php echo formatPrice($stat['revenue']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>