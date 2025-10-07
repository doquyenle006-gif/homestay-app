<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homestay Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .navbar-brand {
            font-weight: bold;
            color: #2c3e50 !important;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        .room-card {
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .room-card:hover {
            transform: translateY(-5px);
        }
        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: #e74c3c;
        }
        .amenities-list {
            list-style: none;
            padding: 0;
        }
        .amenities-list li {
            margin-bottom: 5px;
        }
        .amenities-list li i {
            color: #27ae60;
            margin-right: 8px;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/homestay_v2/index.php">
                <i class="fas fa-home"></i> Homestay Manager
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/homestay_v2/index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/homestay_v2/user/rooms.php">Phòng</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/homestay_v2/user/booking.php">Đặt phòng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/homestay_v2/user/profile.php">Hồ sơ</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-success" href="/homestay_v2/admin/dashboard.php">
                                <i class="fas fa-user-shield"></i> Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <span class="navbar-text me-3">
                                Xin chào, <?php echo $_SESSION['full_name']; ?>!
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/homestay_v2/auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/homestay_v2/auth/login.php">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/homestay_v2/auth/register.php">
                                <i class="fas fa-user-plus"></i> Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?php
        // Display success/error messages if they exist in session
        if (isset($_SESSION['success_message'])) {
            showSuccess($_SESSION['success_message']);
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            showError($_SESSION['error_message']);
            unset($_SESSION['error_message']);
        }
        ?>