<?php
// Include necessary files
include 'config/database.php';
include 'includes/functions.php';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 mb-4">Chào mừng đến với Homestay Manager</h1>
        <p class="lead mb-4">Hệ thống quản lý homestay chuyên nghiệp - Dễ dàng tìm kiếm và đặt phòng</p>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="/homestay_v2/user/rooms.php" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="check_in" id="check_in" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="check_out" id="check_out" required>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="guests">
                            <option value="">Số khách</option>
                            <option value="1">1 khách</option>
                            <option value="2">2 khách</option>
                            <option value="3">3 khách</option>
                            <option value="4">4 khách</option>
                            <option value="5">5+ khách</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-light btn-lg w-100">
                            <i class="fas fa-search"></i> Tìm phòng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Featured Rooms Section -->
<section class="container">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="mb-3">Phòng Nổi Bật</h2>
            <p class="text-muted">Khám phá những phòng homestay được yêu thích nhất</p>
        </div>
    </div>

    <div class="row">
        <?php
        // Get featured rooms (limit to 3)
        $sql = "SELECT * FROM rooms WHERE status = 'available' ORDER BY created_at DESC LIMIT 3";
        $result = executeQuery($sql);
        
        if ($result->num_rows > 0) {
            while ($room = $result->fetch_assoc()) {
                $images = explode(',', $room['images']);
                $first_image = !empty($images[0]) ? $images[0] : 'default-room.jpg';
                $amenities = explode(',', $room['amenities']);
                ?>
                <div class="col-md-4">
                    <div class="card room-card shadow-sm">
                        <img src="/homestay_v2/assets/images/<?php echo $first_image; ?>" class="card-img-top" alt="<?php echo $room['name']; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $room['name']; ?></h5>
                            <p class="card-text text-muted"><?php echo substr($room['description'], 0, 100); ?>...</p>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user-friends"></i> Tối đa <?php echo $room['capacity']; ?> khách
                                </small>
                            </div>
                            <ul class="amenities-list">
                                <?php foreach(array_slice($amenities, 0, 3) as $amenity): ?>
                                    <li><i class="fas fa-check"></i> <?php echo trim($amenity); ?></li>
                                <?php endforeach; ?>
                                <?php if (count($amenities) > 3): ?>
                                    <li><i class="fas fa-plus"></i> Và <?php echo count($amenities) - 3; ?> tiện nghi khác</li>
                                <?php endif; ?>
                            </ul>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price-tag"><?php echo formatPrice($room['price_per_night']); ?>/đêm</span>
                                <a href="/homestay_v2/user/rooms.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12 text-center"><p>Chưa có phòng nào được thêm vào hệ thống.</p></div>';
        }
        ?>
    </div>

    <div class="row mt-5">
        <div class="col-12 text-center">
            <a href="/homestay_v2/user/rooms.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-list"></i> Xem tất cả phòng
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="bg-light py-5 mt-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="feature-box p-4">
                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                    <h4>Tìm Kiếm Dễ Dàng</h4>
                    <p class="text-muted">Tìm phòng homestay phù hợp với nhu cầu và ngân sách của bạn</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box p-4">
                    <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                    <h4>Đặt Phòng Nhanh Chóng</h4>
                    <p class="text-muted">Đặt phòng chỉ với vài cú click, xác nhận ngay lập tức</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box p-4">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h4>An Toàn & Bảo Mật</h4>
                    <p class="text-muted">Hệ thống bảo mật cao, thông tin cá nhân được bảo vệ</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>