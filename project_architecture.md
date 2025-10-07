# Homestay Management Website - Project Architecture

## Project Overview
A PHP-based homestay management system with MySQL database, featuring user authentication, room management, booking system, and admin dashboard.

## Technology Stack
- **Backend**: PHP (without MVC pattern as requested)
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript, Bootstrap
- **Server**: XAMPP (Apache + MySQL)

## Database Schema

### Tables Structure

#### Users Table
```sql
users (id, username, email, password, full_name, phone, role, created_at, updated_at)
```

#### Rooms Table
```sql
rooms (id, name, description, price_per_night, capacity, amenities, images, status, created_at, updated_at)
```

#### Bookings Table
```sql
bookings (id, user_id, room_id, check_in_date, check_out_date, total_price, status, created_at, updated_at)
```

#### Cart Table
```sql
cart (id, user_id, room_id, check_in_date, check_out_date, created_at)
```

## Project Structure
```
homestay_v2/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── auth.php
│   └── functions.php
├── admin/
│   ├── dashboard.php
│   ├── rooms.php
│   ├── bookings.php
│   └── users.php
├── user/
│   ├── index.php
│   ├── rooms.php
│   ├── booking.php
│   └── profile.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
└── index.php
```

## Core Features

### User Features
1. **Authentication System**
   - User registration with validation
   - Secure login/logout
   - Session management

2. **Room Browsing & Search**
   - View available rooms
   - Search by date, price, capacity
   - Room details with images

3. **Booking System**
   - Add rooms to cart
   - Booking process with date selection
   - Booking confirmation

### Admin Features
1. **Dashboard**
   - Statistics and overview
   - Recent bookings
   - Revenue summary

2. **CRUD Operations**
   - Room management (add, edit, delete)
   - Booking management
   - User management

3. **Statistics & Reports**
   - Booking statistics
   - Revenue reports
   - Occupancy rates

## Security Considerations
- Password hashing using password_hash()
- SQL injection prevention with prepared statements
- XSS protection with htmlspecialchars()
- Session security
- Input validation and sanitization

## UI/UX Design Principles
- Responsive design with Bootstrap
- Professional color scheme
- Intuitive navigation
- Mobile-friendly interface
- Clear call-to-action buttons