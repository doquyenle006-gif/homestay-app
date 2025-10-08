<?php
// Common utility functions

// Start session if not already started
if (!function_exists('startSession')) {
    function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}

// Check if user is logged in
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        startSession();
        return isset($_SESSION['user_id']);
    }
}

// Check if user is admin
if (!function_exists('isAdmin')) {
    function isAdmin() {
        startSession();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

// Redirect to another page
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}

// Sanitize input data
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

// Format price
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 2) . ' VND';
    }
}

// Calculate number of nights between two dates
if (!function_exists('calculateNights')) {
    function calculateNights($check_in, $check_out) {
        $start = new DateTime($check_in);
        $end = new DateTime($check_out);
        $interval = $start->diff($end);
        return $interval->days;
    }
}

// Calculate total price
if (!function_exists('calculateTotalPrice')) {
    function calculateTotalPrice($price_per_night, $check_in, $check_out) {
        $nights = calculateNights($check_in, $check_out);
        return $price_per_night * $nights;
    }
}

// Get user information
if (!function_exists('getUserInfo')) {
    function getUserInfo($user_id) {
        global $conn;
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}

// Get room information
if (!function_exists('getRoomInfo')) {
    function getRoomInfo($room_id) {
        global $conn;
        $sql = "SELECT * FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}

// Check if room is available for given dates
if (!function_exists('isRoomAvailable')) {
    function isRoomAvailable($room_id, $check_in, $check_out) {
        global $conn;
        $sql = "SELECT COUNT(*) as count FROM bookings
                WHERE room_id = ?
                AND status IN ('pending', 'confirmed')
                AND ((check_in_date <= ? AND check_out_date >= ?)
                     OR (check_in_date <= ? AND check_out_date >= ?)
                     OR (check_in_date >= ? AND check_out_date <= ?))";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $room_id, $check_out, $check_in, $check_in, $check_out, $check_in, $check_out);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] == 0;
    }
}

// Display success message
if (!function_exists('showSuccess')) {
    function showSuccess($message) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

// Display error message
if (!function_exists('showError')) {
    function showError($message) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

// Function to copy images to assets/images directory
if (!function_exists('copyImageToAssets')) {
    function copyImageToAssets($source_path, $filename = null) {
        $assets_dir = 'assets/images/';
        
        // Ensure assets directory exists
        if (!is_dir($assets_dir)) {
            if (!mkdir($assets_dir, 0755, true)) {
                return false;
            }
        }
        
        // Generate unique filename if not provided
        if (!$filename) {
            $file_extension = pathinfo($source_path, PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
        }
        
        $destination_path = $assets_dir . $filename;
        
        // Copy the file
        if (copy($source_path, $destination_path)) {
            return $filename;
        }
        
        return false;
    }
}

// Function to handle multiple image uploads
if (!function_exists('handleImageUploads')) {
    function handleImageUploads($files_input_name) {
        $uploaded_images = [];
        
        if (isset($_FILES[$files_input_name]) && !empty($_FILES[$files_input_name]['name'][0])) {
            $upload_dir = 'assets/images/';
            
            // Ensure upload directory exists
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    return ['error' => "Không thể tạo thư mục upload: $upload_dir"];
                }
            }
            
            if (!is_writable($upload_dir)) {
                return ['error' => "Thư mục upload không có quyền ghi: $upload_dir"];
            }
            
            // Process each uploaded file
            foreach ($_FILES[$files_input_name]['tmp_name'] as $key => $tmp_name) {
                if ($_FILES[$files_input_name]['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES[$files_input_name]['name'][$key];
                    $file_size = $_FILES[$files_input_name]['size'][$key];
                    $file_type = $_FILES[$files_input_name]['type'][$key];
                    
                    // Validate file exists in temp directory
                    if (empty($tmp_name) || !file_exists($tmp_name)) {
                        continue;
                    }
                    
                    // Validate file type
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!in_array($file_type, $allowed_types)) {
                        continue;
                    }
                    
                    // Validate file size (2MB max)
                    if ($file_size > 2 * 1024 * 1024) {
                        continue;
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    // Copy file to assets/images directory
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        if (file_exists($upload_path)) {
                            $uploaded_images[] = $new_filename;
                        }
                    }
                }
            }
        }
        
        return $uploaded_images;
    }
}
?>