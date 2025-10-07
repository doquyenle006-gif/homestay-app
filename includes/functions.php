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
?>