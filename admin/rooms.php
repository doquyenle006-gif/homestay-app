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
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Delete room
if ($action == 'delete' && $room_id > 0) {
    $sql = "DELETE FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Xóa phòng thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi xóa phòng!";
    }
    redirect('rooms.php');
}

// Toggle room status
if ($action == 'toggle_status' && $room_id > 0) {
    $sql = "UPDATE rooms SET status = CASE WHEN status = 'available' THEN 'unavailable' ELSE 'available' END WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Cập nhật trạng thái phòng thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật trạng thái!";
    }
    redirect('rooms.php');
}

// Process add/edit room form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price_per_night = floatval($_POST['price_per_night']);
    $capacity = intval($_POST['capacity']);
    $amenities = sanitizeInput($_POST['amenities']);
    $status = sanitizeInput($_POST['status']);
    
    // Validate input
    $errors = [];
    
    if (empty($name) || empty($description) || $price_per_night <= 0 || $capacity <= 0) {
        $errors[] = "Vui lòng điền đầy đủ thông tin bắt buộc!";
    }
    
    if (empty($errors)) {
        if ($room_id > 0) {
            // Update existing room
            $sql = "UPDATE rooms SET name = ?, description = ?, price_per_night = ?, capacity = ?, amenities = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdissi", $name, $description, $price_per_night, $capacity, $amenities, $status, $room_id);
            $success_message = "Cập nhật phòng thành công!";
        } else {
            // Add new room
            $sql = "INSERT INTO rooms (name, description, price_per_night, capacity, amenities, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiss", $name, $description, $price_per_night, $capacity, $amenities, $status);
            $success_message = "Thêm phòng mới thành công!";
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = $success_message;
            redirect('rooms.php');
        } else {
            $errors[] = "Có lỗi xảy ra khi lưu thông tin phòng!";
        }
    }
}

// Get room data for editing
$room = null;
if ($room_id > 0 && $action == 'edit') {
    $sql = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
}

// Get all rooms
$rooms_sql = "SELECT * FROM rooms ORDER BY created_at DESC";
$rooms_result = executeQuery($rooms_sql);

include '../includes/admin_header.php';

// If we're in add/edit mode, redirect to form page
if ($action == 'add' || ($action == 'edit' && $room_id > 0)) {
    // Define constant to indicate room_form.php is being included
    define('ROOMS_PAGE_INCLUDED', true);
    include 'room_form.php';
    exit;
}

?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2>Quản Lý Phòng</h2>
                    <p class="text-muted">Thêm, sửa, xóa và quản lý các phòng homestay</p>
                </div>
                <a href="rooms.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm Phòng Mới
                </a>
            </div>
        </div>
    </div>

    <!-- Rooms List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách phòng</h5>
                </div>
                <div class="card-body">
                    <?php if ($rooms_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tên phòng</th>
                                        <th>Giá/đêm</th>
                                        <th>Sức chứa</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($room_item = $rooms_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $room_item['name']; ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo substr($room_item['description'], 0, 50); ?>...</small>
                                            </td>
                                            <td><?php echo formatPrice($room_item['price_per_night']); ?></td>
                                            <td><?php echo $room_item['capacity']; ?> khách</td>
                                            <td>
                                                <span class="badge <?php echo $room_item['status'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $room_item['status'] == 'available' ? 'Có sẵn' : 'Không khả dụng'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($room_item['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="rooms.php?action=edit&id=<?php echo $room_item['id']; ?>" 
                                                       class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="rooms.php?action=toggle_status&id=<?php echo $room_item['id']; ?>" 
                                                       class="btn btn-outline-warning">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </a>
                                                    <a href="rooms.php?action=delete&id=<?php echo $room_item['id']; ?>" 
                                                       class="btn btn-outline-danger" 
                                                       onclick="return confirm('Bạn có chắc chắn muốn xóa phòng này?')">
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
                        <p class="text-muted">Chưa có phòng nào được thêm vào hệ thống.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>