<?php
// Check if this file is being included from rooms.php
$is_included = defined('ROOMS_PAGE_INCLUDED');

// If not included, include necessary files and header
if (!$is_included) {
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

    // Get room data for editing
    $room = null;
    if ($room_id > 0 && $action == 'edit') {
        $sql = "SELECT * FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $room = $result->fetch_assoc();
        
        if (!$room) {
            $_SESSION['error_message'] = "Phòng không tồn tại!";
            redirect('rooms.php');
        }
    }

    // Process form submission
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
        
        // Handle image uploads using the new function
        $uploaded_images = handleImageUploads('images');
        
        // Check if there was an error with uploads
        if (isset($uploaded_images['error'])) {
            $errors[] = $uploaded_images['error'];
        }
        
        if (empty($errors)) {
            // Handle existing images for update
            $current_images = [];
            if ($room_id > 0 && $room) {
                $current_images = !empty($room['images']) ? explode(',', $room['images']) : [];
                
                // Remove images marked for deletion
                if (isset($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $image_to_delete) {
                        $image_path = $upload_dir . trim($image_to_delete);
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                        $current_images = array_diff($current_images, [trim($image_to_delete)]);
                    }
                }
            }
            
            // Combine existing and new images
            $all_images = array_merge($current_images, $uploaded_images);
            $images_string = !empty($all_images) ? implode(',', $all_images) : '';
            
            if ($room_id > 0) {
                // Update existing room
                $sql = "UPDATE rooms SET name = ?, description = ?, price_per_night = ?, capacity = ?, amenities = ?, status = ?, images = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdissis", $name, $description, $price_per_night, $capacity, $amenities, $status, $images_string, $room_id);
                $success_message = "Cập nhật phòng thành công!";
            } else {
                // Add new room
                $sql = "INSERT INTO rooms (name, description, price_per_night, capacity, amenities, status, images) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdissis", $name, $description, $price_per_night, $capacity, $amenities, $status, $images_string);
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

    include '../includes/admin_header.php';
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/homestay_v2/admin/rooms.php">Quản lý phòng</a></li>
                    <li class="breadcrumb-item active">
                        <?php echo $room_id > 0 ? 'Chỉnh sửa phòng' : 'Thêm phòng mới'; ?>
                    </li>
                </ol>
            </nav>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-<?php echo $room_id > 0 ? 'edit' : 'plus'; ?> text-primary"></i>
                    <?php echo $room_id > 0 ? 'Chỉnh sửa phòng' : 'Thêm phòng mới'; ?>
                </h2>
                <a href="rooms.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                </a>
            </div>

            <!-- Room Form -->
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra:</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên phòng *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo $room ? $room['name'] : ''; ?>" 
                                           placeholder="Nhập tên phòng" required>
                                    <div class="form-text">Tên phòng phải là duy nhất và dễ nhận biết.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price_per_night" class="form-label">Giá mỗi đêm (VND) *</label>
                                    <input type="number" class="form-control" id="price_per_night" name="price_per_night" 
                                           value="<?php echo $room ? $room['price_per_night'] : ''; ?>" 
                                           min="0" step="0.01" placeholder="0.00" required>
                                    <div class="form-text">Giá cho mỗi đêm thuê phòng.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Sức chứa (số khách) *</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" 
                                           value="<?php echo $room ? $room['capacity'] : ''; ?>" 
                                           min="1" max="20" placeholder="2" required>
                                    <div class="form-text">Số lượng khách tối đa có thể ở trong phòng.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="available" <?php echo ($room && $room['status'] == 'available') ? 'selected' : ''; ?>>Có sẵn</option>
                                        <option value="unavailable" <?php echo ($room && $room['status'] == 'unavailable') ? 'selected' : ''; ?>>Không khả dụng</option>
                                    </select>
                                    <div class="form-text">Trạng thái hiển thị của phòng.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Mô tả chi tiết về phòng, tiện nghi, view..." required><?php echo $room ? $room['description'] : ''; ?></textarea>
                            <div class="form-text">Mô tả chi tiết giúp khách hàng hiểu rõ về phòng.</div>
                        </div>

                        <div class="mb-3">
                            <label for="amenities" class="form-label">Tiện nghi</label>
                            <textarea class="form-control" id="amenities" name="amenities" rows="3"
                                      placeholder="WiFi, AC, TV, Mini Bar, Sea View..."><?php echo $room ? $room['amenities'] : ''; ?></textarea>
                            <div class="form-text">Mỗi tiện nghi trên một dòng, phân cách bằng dấu phẩy.</div>
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Hình ảnh phòng</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                            <div class="form-text">Chọn một hoặc nhiều hình ảnh cho phòng. Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB mỗi ảnh.</div>
                            
                            <?php if ($room && !empty($room['images'])): ?>
                                <div class="mt-3">
                                    <label class="form-label">Hình ảnh hiện tại:</label>
                                    <div class="row">
                                        <?php
                                        $current_images = explode(',', $room['images']);
                                        foreach ($current_images as $image):
                                            if (!empty(trim($image))):
                                        ?>
                                            <div class="col-md-3 mb-2">
                                                <div class="position-relative">
                                                    <img src="../assets/images/<?php echo trim($image); ?>"
                                                         class="img-fluid rounded border"
                                                         style="height: 100px; object-fit: cover; width: 100%;"
                                                         alt="Room image">
                                                    <div class="form-check position-absolute top-0 start-0 m-1">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="delete_images[]" value="<?php echo trim($image); ?>">
                                                        <label class="form-check-label text-white bg-dark px-1 rounded">
                                                            Xóa
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="rooms.php" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> 
                                <?php echo $room_id > 0 ? 'Cập nhật phòng' : 'Thêm phòng mới'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Room Preview (for edit mode) -->
            <?php if ($room_id > 0): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-eye"></i> Xem trước</h5>
                </div>
                <div class="card-body">
                    <!-- Room Images -->
                    <?php if (!empty($room['images'])): ?>
                    <div class="mb-4">
                        <h6>Hình ảnh phòng:</h6>
                        <div class="row">
                            <?php
                            $preview_images = explode(',', $room['images']);
                            foreach ($preview_images as $image):
                                if (!empty(trim($image))):
                            ?>
                                <div class="col-md-3 mb-3">
                                    <img src="../assets/images/<?php echo trim($image); ?>"
                                         class="img-fluid rounded shadow-sm"
                                         style="height: 150px; object-fit: cover; width: 100%;"
                                         alt="Room image">
                                </div>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <h5><?php echo $room['name']; ?></h5>
                            <p class="text-muted"><?php echo $room['description']; ?></p>
                            <div class="mb-3">
                                <strong>Tiện nghi:</strong>
                                <div class="amenities-list">
                                    <?php
                                    $amenities_list = explode(',', $room['amenities']);
                                    foreach ($amenities_list as $amenity):
                                    ?>
                                        <span class="badge bg-light text-dark me-1 mb-1"><?php echo trim($amenity); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <h4 class="text-primary"><?php echo formatPrice($room['price_per_night']); ?>/đêm</h4>
                                <p><i class="fas fa-user-friends"></i> Tối đa <?php echo $room['capacity']; ?> khách</p>
                                <span class="badge <?php echo $room['status'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $room['status'] == 'available' ? 'Có sẵn' : 'Không khả dụng'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>