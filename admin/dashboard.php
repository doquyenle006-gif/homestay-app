<?php
// Include necessary files
include '../config/database.php';
include '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    $_SESSION['error_message'] = "Bạn không có quyền truy cập trang quản trị!";
    redirect('../index.php');
}

// Get statistics
$total_users = executeQuery("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_rooms = executeQuery("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
$total_bookings = executeQuery("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$pending_bookings = executeQuery("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'];

// Get revenue statistics
$revenue_sql = "SELECT SUM(total_price) as revenue FROM bookings WHERE status = 'confirmed'";
$revenue_result = executeQuery($revenue_sql);
$total_revenue = $revenue_result->fetch_assoc()['revenue'] ?? 0;

// Get recent bookings
$recent_bookings_sql = "SELECT b.*, u.full_name, r.name as room_name 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.id 
                        JOIN rooms r ON b.room_id = r.id 
                        ORDER BY b.created_at DESC 
                        LIMIT 5";
$recent_bookings = executeQuery($recent_bookings_sql);

include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Dashboard Quản Trị</h2>
            <p class="text-muted">Tổng quan về hệ thống homestay</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_users; ?></h4>
                            <p class="mb-0">Tổng người dùng</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_rooms; ?></h4>
                            <p class="mb-0">Tổng phòng</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bed fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $total_bookings; ?></h4>
                            <p class="mb-0">Tổng đặt phòng</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo formatPrice($total_revenue); ?></h4>
                            <p class="mb-0">Tổng doanh thu</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Thống kê đặt phòng</h5>
                </div>
                <div class="card-body">
                    <?php
                    $booking_stats_sql = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status";
                    $booking_stats = executeQuery($booking_stats_sql);
                    ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Trạng thái</th>
                                    <th>Số lượng</th>
                                    <th>Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($stat = $booking_stats->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            switch ($stat['status']) {
                                                case 'confirmed': echo 'Đã xác nhận'; break;
                                                case 'pending': echo 'Đang chờ'; break;
                                                case 'cancelled': echo 'Đã hủy'; break;
                                                default: echo $stat['status'];
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $stat['count']; ?></td>
                                        <td>
                                            <?php 
                                            $percentage = $total_bookings > 0 ? ($stat['count'] / $total_bookings) * 100 : 0;
                                            echo number_format($percentage, 1) . '%';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Đặt phòng gần đây</h5>
                </div>
                <div class="card-body">
                    <?php if ($recent_bookings->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th>Phòng</th>
                                        <th>Ngày đặt</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $booking['full_name']; ?></td>
                                            <td><?php echo $booking['room_name']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($booking['created_at'])); ?></td>
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
                    <?php else: ?>
                        <p class="text-muted">Chưa có đặt phòng nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include '../includes/admin_footer.php'; ?>