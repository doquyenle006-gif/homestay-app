<?php
// Include necessary files
include '../config/database.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Vui lòng đăng nhập để đặt phòng!";
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Get room_id from URL if provided
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

// Get room information if room_id is provided
$room = null;
if ($room_id > 0) {
    $sql = "SELECT * FROM rooms WHERE id = ? AND status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
}

// Process booking form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = intval($_POST['room_id']);
    $check_in = sanitizeInput($_POST['check_in_date']);
    $check_out = sanitizeInput($_POST['check_out_date']);
    
    // Validate input
    $errors = [];
    
    if (empty($room_id) || empty($check_in) || empty($check_out)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin!";
    }
    
    if ($check_in >= $check_out) {
        $errors[] = "Ngày trả phòng phải sau ngày nhận phòng!";
    }
    
    // Check room availability
    if (!isRoomAvailable($room_id, $check_in, $check_out)) {
        $errors[] = "Phòng đã được đặt trong khoảng thời gian này!";
    }
    
    // Get room price
    $room_sql = "SELECT price_per_night FROM rooms WHERE id = ?";
    $room_stmt = $conn->prepare($room_sql);
    $room_stmt->bind_param("i", $room_id);
    $room_stmt->execute();
    $room_result = $room_stmt->get_result();
    $room_data = $room_result->fetch_assoc();
    
    if (!$room_data) {
        $errors[] = "Phòng không tồn tại hoặc không khả dụng!";
    }
    
    // If no errors, create booking
    if (empty($errors)) {
        $total_price = calculateTotalPrice($room_data['price_per_night'], $check_in, $check_out);
        
        $sql = "INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, total_price, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissd", $user_id, $room_id, $check_in, $check_out, $total_price);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Đặt phòng thành công! Vui lòng chờ xác nhận từ quản trị viên.";
            redirect('index.php');
        } else {
            $errors[] = "Có lỗi xảy ra khi đặt phòng. Vui lòng thử lại!";
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Đặt Phòng Homestay</h2>
            <p class="text-muted">Đặt phòng homestay của bạn một cách dễ dàng</p>
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
        <!-- Booking Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Thông tin đặt phòng</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="room_id" class="form-label">Chọn phòng *</label>
                                    <select class="form-select" id="room_id" name="room_id" required>
                                        <option value="">-- Chọn phòng --</option>
                                        <?php
                                        $rooms_sql = "SELECT * FROM rooms WHERE status = 'available' ORDER BY name";
                                        $rooms_result = executeQuery($rooms_sql);
                                        while ($room_option = $rooms_result->fetch_assoc()) {
                                            $selected = ($room_option['id'] == $room_id) ? 'selected' : '';
                                            echo "<option value='{$room_option['id']}' $selected>
                                                    {$room_option['name']} - " . formatPrice($room_option['price_per_night']) . "/đêm
                                                  </option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Giá phòng</label>
                                    <div id="room_price" class="form-control bg-light">
                                        <?php if ($room): ?>
                                            <?php echo formatPrice($room['price_per_night']); ?>/đêm
                                        <?php else: ?>
                                            Vui lòng chọn phòng
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_in_date" class="form-label">Ngày nhận phòng *</label>
                                    <input type="date" class="form-control" id="check_in_date" name="check_in_date" 
                                           value="<?php echo isset($_POST['check_in_date']) ? $_POST['check_in_date'] : ''; ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out_date" class="form-label">Ngày trả phòng *</label>
                                    <input type="date" class="form-control" id="check_out_date" name="check_out_date" 
                                           value="<?php echo isset($_POST['check_out_date']) ? $_POST['check_out_date'] : ''; ?>" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số đêm</label>
                                    <div id="nights_count" class="form-control bg-light">0 đêm</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tổng tiền</label>
                                    <div id="total_price" class="form-control bg-light">0 VND</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-calendar-check"></i> Đặt Phòng Ngay
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Room Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin phòng</h5>
                </div>
                <div class="card-body">
                    <?php if ($room): ?>
                        <h6><?php echo $room['name']; ?></h6>
                        <p class="text-muted"><?php echo $room['description']; ?></p>
                        
                        <div class="mb-3">
                            <strong>Tiện nghi:</strong>
                            <ul class="amenities-list">
                                <?php 
                                $amenities = explode(',', $room['amenities']);
                                foreach ($amenities as $amenity): 
                                ?>
                                    <li><i class="fas fa-check text-success"></i> <?php echo trim($amenity); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Sức chứa:</strong>
                            <p><i class="fas fa-user-friends"></i> Tối đa <?php echo $room['capacity']; ?> khách</p>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Giá:</strong>
                            <h5 class="text-primary"><?php echo formatPrice($room['price_per_night']); ?>/đêm</h5>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Vui lòng chọn một phòng để xem thông tin chi tiết.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update price calculation when dates or room selection changes
function updatePriceCalculation() {
    const roomSelect = document.getElementById('room_id');
    const checkIn = document.getElementById('check_in_date');
    const checkOut = document.getElementById('check_out_date');
    const nightsCount = document.getElementById('nights_count');
    const totalPrice = document.getElementById('total_price');
    const roomPrice = document.getElementById('room_price');
    
    // Get selected room price
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    let pricePerNight = 0;
    if (selectedOption.value) {
        const priceText = selectedOption.text.split(' - ')[1];
        if (priceText) {
            pricePerNight = parseFloat(priceText.replace(/[^0-9.]/g, ''));
        }
        roomPrice.textContent = pricePerNight.toLocaleString() + ' VND/đêm';
    } else {
        roomPrice.textContent = 'Vui lòng chọn phòng';
    }
    
    // Calculate nights and total price
    if (checkIn.value && checkOut.value && pricePerNight > 0) {
        const start = new Date(checkIn.value);
        const end = new Date(checkOut.value);
        const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        
        if (nights > 0) {
            nightsCount.textContent = nights + ' đêm';
            totalPrice.textContent = (nights * pricePerNight).toLocaleString() + ' VND';
        } else {
            nightsCount.textContent = '0 đêm';
            totalPrice.textContent = '0 VND';
        }
    } else {
        nightsCount.textContent = '0 đêm';
        totalPrice.textContent = '0 VND';
    }
}

// Add event listeners
document.getElementById('room_id').addEventListener('change', updatePriceCalculation);
document.getElementById('check_in_date').addEventListener('change', updatePriceCalculation);
document.getElementById('check_out_date').addEventListener('change', updatePriceCalculation);

// Initialize calculation
updatePriceCalculation();
</script>

<?php include '../includes/footer.php'; ?>