<?php
// Include necessary files
include '../config/database.php';
include '../includes/functions.php';

// Get search parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$check_in = isset($_GET['check_in']) ? sanitizeInput($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitizeInput($_GET['check_out']) : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 0;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 1000;

// Build SQL query with filters
$sql = "SELECT * FROM rooms WHERE status = 'available'";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if ($guests > 0) {
    $sql .= " AND capacity >= ?";
    $params[] = $guests;
    $types .= "i";
}

if ($min_price > 0) {
    $sql .= " AND price_per_night >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price > 0 && $max_price < 10000) {
    $sql .= " AND price_per_night <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$sql .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Danh Sách Phòng Homestay</h2>
            <p class="text-muted">Tìm phòng homestay phù hợp với nhu cầu của bạn</p>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Bộ lọc tìm kiếm</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Tìm kiếm</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo $search; ?>" placeholder="Tên phòng, mô tả...">
                            </div>
                            <div class="col-md-2">
                                <label for="check_in" class="form-label">Ngày nhận</label>
                                <input type="date" class="form-control" id="check_in" name="check_in" 
                                       value="<?php echo $check_in; ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="check_out" class="form-label">Ngày trả</label>
                                <input type="date" class="form-control" id="check_out" name="check_out" 
                                       value="<?php echo $check_out; ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="guests" class="form-label">Số khách</label>
                                <select class="form-select" id="guests" name="guests">
                                    <option value="">Tất cả</option>
                                    <option value="1" <?php echo $guests == 1 ? 'selected' : ''; ?>>1 khách</option>
                                    <option value="2" <?php echo $guests == 2 ? 'selected' : ''; ?>>2 khách</option>
                                    <option value="3" <?php echo $guests == 3 ? 'selected' : ''; ?>>3 khách</option>
                                    <option value="4" <?php echo $guests == 4 ? 'selected' : ''; ?>>4 khách</option>
                                    <option value="5" <?php echo $guests == 5 ? 'selected' : ''; ?>>5+ khách</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Khoảng giá (VND/đêm)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="min_price" 
                                               value="<?php echo $min_price; ?>" placeholder="Từ" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="max_price" 
                                               value="<?php echo $max_price; ?>" placeholder="Đến" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                                <a href="/homestay_v2/user/rooms.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh"></i> Xóa bộ lọc
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Room List -->
    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($room = $result->fetch_assoc()) {
                $images = explode(',', $room['images']);
                $first_image = !empty($images[0]) ? $images[0] : 'default-room.jpg';
                $amenities = explode(',', $room['amenities']);
                
                // Check availability for selected dates
                $is_available = true;
                if (!empty($check_in) && !empty($check_out)) {
                    $is_available = isRoomAvailable($room['id'], $check_in, $check_out);
                }
                ?>
                <div class="col-md-6 mb-4">
                    <div class="card room-card shadow-sm h-100">
                        <div class="row g-0 h-100">
                            <div class="col-md-5">
                                <img src="../assets/images/<?php echo $first_image; ?>" 
                                     class="img-fluid rounded-start h-100 w-100" 
                                     style="object-fit: cover;" 
                                     alt="<?php echo $room['name']; ?>">
                            </div>
                            <div class="col-md-7">
                                <div class="card-body d-flex flex-column h-100">
                                    <h5 class="card-title"><?php echo $room['name']; ?></h5>
                                    <p class="card-text text-muted flex-grow-1">
                                        <?php echo substr($room['description'], 0, 150); ?>...
                                    </p>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user-friends"></i> Tối đa <?php echo $room['capacity']; ?> khách
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small><strong>Tiện nghi:</strong></small>
                                        <div class="amenities-list">
                                            <?php foreach(array_slice($amenities, 0, 3) as $amenity): ?>
                                                <small><i class="fas fa-check text-success"></i> <?php echo trim($amenity); ?></small><br>
                                            <?php endforeach; ?>
                                            <?php if (count($amenities) > 3): ?>
                                                <small><i class="fas fa-plus text-primary"></i> Và <?php echo count($amenities) - 3; ?> tiện nghi khác</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="price-tag"><?php echo formatPrice($room['price_per_night']); ?>/đêm</span>
                                            </div>
                                            <div>
                                                <?php if (!$is_available && !empty($check_in) && !empty($check_out)): ?>
                                                    <span class="badge bg-danger">Đã hết phòng</span>
                                                <?php else: ?>
                                                    <a href="/homestay_v2/user/booking.php?room_id=<?php echo $room['id']; ?>"
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-calendar-plus"></i> Đặt ngay
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12 text-center"><p>Không tìm thấy phòng nào phù hợp với tiêu chí tìm kiếm.</p></div>';
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>