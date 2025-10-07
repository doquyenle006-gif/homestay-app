# Homestay Management Database Schema

## Database: `homestay_db`

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Stored as plain text
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role VARCHAR(10) DEFAULT 'user', -- 'admin' or 'user'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Rooms Table
```sql
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL,
    amenities TEXT,
    images TEXT,
    status VARCHAR(20) DEFAULT 'available', -- 'available' or 'unavailable'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Bookings Table
```sql
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'confirmed', 'cancelled', 'completed'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);
```

### Cart Table
```sql
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);
```

## Sample Data

### Sample Users
```sql
INSERT INTO users (username, email, password, full_name, phone, role) VALUES
('admin', 'admin@homestay.com', 'admin123', 'System Administrator', '0123456789', 'admin'),
('john_doe', 'john@example.com', 'password123', 'John Doe', '0987654321', 'user');
```

### Sample Rooms
```sql
INSERT INTO rooms (name, description, price_per_night, capacity, amenities, images) VALUES
('Deluxe Room', 'Spacious room with sea view', 150.00, 2, 'WiFi, AC, TV, Mini Bar', 'room1.jpg,room2.jpg'),
('Family Suite', 'Perfect for families with children', 250.00, 4, 'WiFi, AC, TV, Kitchenette', 'suite1.jpg'),
('Standard Room', 'Comfortable budget room', 80.00, 2, 'WiFi, AC, TV', 'standard1.jpg');
```

## Indexes for Performance
```sql
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_bookings_dates ON bookings(check_in_date, check_out_date);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_status ON bookings(status);
```

## Relationships Diagram

```mermaid
erDiagram
    users ||--o{ bookings : "makes"
    users ||--o{ cart : "adds to"
    rooms ||--o{ bookings : "booked in"
    rooms ||--o{ cart : "added to"
    
    users {
        int id PK
        string username
        string email
        string password
        string full_name
        string phone
        enum role
        timestamp created_at
        timestamp updated_at
    }
    
    rooms {
        int id PK
        string name
        text description
        decimal price_per_night
        int capacity
        text amenities
        text images
        enum status
        timestamp created_at
        timestamp updated_at
    }
    
    bookings {
        int id PK
        int user_id FK
        int room_id FK
        date check_in_date
        date check_out_date
        decimal total_price
        enum status
        timestamp created_at
        timestamp updated_at
    }
    
    cart {
        int id PK
        int user_id FK
        int room_id FK
        date check_in_date
        date check_out_date
        timestamp created_at
    }