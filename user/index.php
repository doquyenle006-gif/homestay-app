<?php
// Include necessary files
include '../config/database.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Vui lòng đăng nhập để truy cập trang này!";
    redirect('../auth/login.php');
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_info = getUserInfo($user_id);

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Xin chào, <?php echo $user_info['full_name']; ?>!</h2>
            <p class="text-muted">Chào mừng bạn đến với trang quản lý cá nhân</p>
        </div>
    </div>

    <!-- User Stats -->
    <div class="row mt-4">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4><i class="fas fa-bed"></i></h4>
                    <h5>
                        <?php
                        $sql = "SELECT COUNT(*) as total FROM bookings WHERE user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        echo $row['total'];
                        ?>
                    </h5>
                    <p class="mb-0">Tổng đặt phòng</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4><i class="fas fa-check-circle"></i></h4>
                    <h5>
                        <?php
                        $sql = "SELECT COUNT(*) as total FROM bookings WHERE user_id = ? AND status = 'confirmed'";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        echo $row['total'];
                        ?>
                    </h5>
                    <p class="mb-0">Đã xác nhận</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4><i class="fas fa-clock"></i></h4>
                    <h5>
                        <?php
                        $sql = "SELECT COUNT(*) as total FROM bookings WHERE user_id = ? AND status = 'pending'";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        echo $row['total'];
                        ?>
                    </h5>
                    <p class="mb-0">Đang chờ</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4><i class="fas fa-shopping-cart"></i></h4>
                    <h5>
                        <?php
                        $sql = "SELECT COUNT(*) as total FROM cart WHERE user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        echo $row['total'];
                        ?>
                    </h5>
                    <p class="mb-0">Trong giỏ hàng</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Đặt phòng gần đây</h5>
                </div>
                <div class="card-body">
                    <?php
                    $sql = "SELECT b.*, r.name as room_name, r.price_per_night 
                            FROM bookings b 
                            JOIN rooms r ON b.room_id = r.id 
                            WHERE b.user_id = ? 
                            ORDER BY b.created_at DESC 
                            LIMIT 5";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Phòng</th>
                                        <th>Ngày nhận</th>
                                        <th>Ngày trả</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $booking['room_name']; ?></td>
                                            <td><?php echo $booking['check_in_date']; ?></td>
                                            <td><?php echo $booking['check_out_date']; ?></td>
                                            <td><?php echo formatPrice($booking['total_price']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($booking['status']) {
                                                    case 'confirmed':
                                                        $status_class = 'badge bg-success';
                                                        break;
                                                    case 'pending':
                                                        $status_class = 'badge bg-warning';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'badge bg-danger';
                                                        break;
                                                    default:
                                                        $status_class = 'badge bg-secondary';
                                                }
                                                ?>
                                                <span class="<?php echo $status_class; ?>">
                                                    <?php 
                                                    switch ($booking['status']) {
                                                        case 'confirmed': echo 'Đã xác nhận'; break;
                                                        case 'pending': echo 'Đang chờ'; break;
                                                        case 'cancelled': echo 'Đã hủy'; break;
                                                        default: echo $booking['status'];
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    } else {
                        echo '<p class="text-muted">Bạn chưa có đặt phòng nào.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Hành động nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="/homestay_v2/user/rooms.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search"></i> Tìm phòng
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/homestay_v2/user/booking.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-calendar-plus"></i> Đặt phòng
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/homestay_v2/user/profile.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-user-edit"></i> Cập nhật hồ sơ
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="/homestay_v2/auth/logout.php" class="btn btn-outline-danger w-100">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>