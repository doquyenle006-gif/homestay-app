<?php
// Include database configuration
include 'config/database.php';

// Create users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role VARCHAR(10) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (executeQuery($sql_users)) {
    echo "Users table created successfully!<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create rooms table
$sql_rooms = "CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL,
    amenities TEXT,
    images TEXT,
    status VARCHAR(20) DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (executeQuery($sql_rooms)) {
    echo "Rooms table created successfully!<br>";
} else {
    echo "Error creating rooms table: " . $conn->error . "<br>";
}

// Create bookings table
$sql_bookings = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
)";

if (executeQuery($sql_bookings)) {
    echo "Bookings table created successfully!<br>";
} else {
    echo "Error creating bookings table: " . $conn->error . "<br>";
}

// Create cart table
$sql_cart = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
)";

if (executeQuery($sql_cart)) {
    echo "Cart table created successfully!<br>";
} else {
    echo "Error creating cart table: " . $conn->error . "<br>";
}

// Insert sample admin user
$sql_admin = "INSERT IGNORE INTO users (username, email, password, full_name, phone, role) 
              VALUES ('admin', 'admin@homestay.com', 'admin123', 'System Administrator', '0123456789', 'admin')";

if (executeQuery($sql_admin)) {
    if ($conn->affected_rows > 0) {
        echo "Admin user created successfully!<br>";
    } else {
        echo "Admin user already exists!<br>";
    }
} else {
    echo "Error creating admin user: " . $conn->error . "<br>";
}

// Insert sample rooms
$sql_sample_rooms = "INSERT IGNORE INTO rooms (name, description, price_per_night, capacity, amenities, images) VALUES
    ('Deluxe Room', 'Spacious room with sea view, perfect for couples', 150.00, 2, 'WiFi, AC, TV, Mini Bar, Sea View', 'deluxe1.jpg,deluxe2.jpg'),
    ('Family Suite', 'Large suite perfect for families with children', 250.00, 4, 'WiFi, AC, TV, Kitchenette, Balcony', 'family1.jpg,family2.jpg'),
    ('Standard Room', 'Comfortable budget-friendly room', 80.00, 2, 'WiFi, AC, TV', 'standard1.jpg'),
    ('Luxury Villa', 'Private villa with pool and garden', 500.00, 6, 'WiFi, AC, TV, Private Pool, Garden, Kitchen', 'villa1.jpg,villa2.jpg,villa3.jpg')";

if (executeQuery($sql_sample_rooms)) {
    if ($conn->affected_rows > 0) {
        echo "Sample rooms created successfully!<br>";
    } else {
        echo "Sample rooms already exist!<br>";
    }
} else {
    echo "Error creating sample rooms: " . $conn->error . "<br>";
}

echo "<br>Database setup completed! You can now access the website.";

// Close connection
$conn->close();
?>