# Homestay Management System

Một hệ thống quản lý homestay được xây dựng bằng PHP và MySQL, cung cấp đầy đủ các tính năng quản lý cho cả người dùng và quản trị viên.

## Tính năng chính

### Cho người dùng
- ✅ Đăng ký, đăng nhập, đăng xuất
- ✅ Xem danh sách phòng với tìm kiếm và lọc
- ✅ Đặt phòng với tính năng kiểm tra phòng trống
- ✅ Quản lý hồ sơ cá nhân
- ✅ Xem lịch sử đặt phòng

### Cho quản trị viên
- ✅ Dashboard với thống kê tổng quan
- ✅ Quản lý phòng (CRUD đầy đủ)
- ✅ Quản lý đặt phòng (xác nhận, hủy, hoàn thành)
- ✅ Quản lý người dùng (phân quyền, xóa)
- ✅ Thống kê và báo cáo

## Công nghệ sử dụng

- **Backend**: PHP thuần (không dùng MVC)
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5
- **Icons**: Font Awesome
- **Server**: XAMPP (Apache + MySQL)

## Cài đặt và chạy ứng dụng

### 1. Yêu cầu hệ thống
- XAMPP (Apache + MySQL)
- PHP 7.4 trở lên
- MySQL 5.7 trở lên

### 2. Các bước cài đặt

#### Bước 1: Clone/Copy project
Đặt toàn bộ mã nguồn vào thư mục `htdocs` của XAMPP:
```
C:\xampp\htdocs\homestay_v2\
```

#### Bước 2: Khởi động dịch vụ
- Mở XAMPP Control Panel
- Start Apache và MySQL

#### Bước 3: Thiết lập database
1. Mở trình duyệt và truy cập:
   ```
   http://localhost/homestay_v2/setup_database.php
   ```
2. Hệ thống sẽ tự động tạo database và các bảng cần thiết
3. Dữ liệu mẫu sẽ được thêm vào (phòng, admin user)

#### Bước 4: Truy cập ứng dụng
- **Trang chủ**: `http://localhost/homestay_v2/`
- **Đăng nhập**: `http://localhost/homestay_v2/auth/login.php`

### 3. Tài khoản demo

#### Quản trị viên
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@homestay.com`

#### Người dùng thường
- **Username**: `john_doe`
- **Password**: `password123`
- **Email**: `john@example.com`

## Cấu trúc thư mục

```
homestay_v2/
├── config/
│   └── database.php          # Cấu hình database
├── includes/
│   ├── header.php            # Header chung
│   ├── footer.php            # Footer chung
│   └── functions.php         # Hàm tiện ích
├── auth/
│   ├── login.php             # Đăng nhập
│   ├── register.php          # Đăng ký
│   └── logout.php            # Đăng xuất
├── user/
│   ├── index.php             # Dashboard người dùng
│   ├── rooms.php             # Danh sách phòng
│   ├── booking.php           # Đặt phòng
│   └── profile.php           # Hồ sơ cá nhân
├── admin/
│   ├── dashboard.php         # Dashboard quản trị
│   ├── rooms.php             # Quản lý phòng
│   ├── bookings.php          # Quản lý đặt phòng
│   └── users.php             # Quản lý người dùng
├── assets/
│   └── css/
│       └── style.css         # CSS tùy chỉnh
├── setup_database.php        # Script tạo database
└── index.php                 # Trang chủ
```

## Tính năng bảo mật

- **SQL Injection**: Sử dụng prepared statements
- **XSS Protection**: Sử dụng `htmlspecialchars()` và `sanitizeInput()`
- **Session Security**: Quản lý session an toàn
- **Input Validation**: Kiểm tra dữ liệu đầu vào
- **Role-based Access**: Phân quyền truy cập

## Tính năng đặc biệt

### Hệ thống đặt phòng
- Kiểm tra phòng trống theo ngày
- Tính toán tự động số đêm và tổng tiền
- Xác nhận đặt phòng từ admin

### Tìm kiếm và lọc
- Tìm kiếm theo tên phòng, mô tả
- Lọc theo giá, số khách, ngày
- Hiển thị trạng thái phòng trống

### Thống kê và báo cáo
- Thống kê doanh thu
- Phân tích tỷ lệ đặt phòng
- Báo cáo người dùng hoạt động

## Hướng dẫn sử dụng

### Cho người dùng
1. **Đăng ký tài khoản**: Truy cập trang đăng ký và điền thông tin
2. **Tìm phòng**: Sử dụng bộ lọc để tìm phòng phù hợp
3. **Đặt phòng**: Chọn ngày nhận/trả và xác nhận đặt phòng
4. **Theo dõi**: Xem trạng thái đặt phòng trong dashboard

### Cho quản trị viên
1. **Quản lý phòng**: Thêm, sửa, xóa phòng và cập nhật trạng thái
2. **Quản lý đặt phòng**: Xác nhận, hủy, hoàn thành đơn đặt phòng
3. **Quản lý người dùng**: Phân quyền và quản lý tài khoản
4. **Theo dõi thống kê**: Xem báo cáo tổng quan hệ thống

## Tùy chỉnh và mở rộng

### Thay đổi cấu hình database
Sửa file `config/database.php`:
```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'homestay_db';
```

### Thêm tính năng mới
- Thêm trường vào bảng trong database
- Tạo file PHP mới trong thư mục phù hợp
- Cập nhật navigation trong `includes/header.php`

## Khắc phục sự cố

### Lỗi kết nối database
- Kiểm tra XAMPP đã chạy chưa
- Xác nhận thông tin kết nối trong `config/database.php`

### Lỗi không tìm thấy trang
- Kiểm tra đường dẫn file
- Đảm bảo file tồn tại trong thư mục đúng

### Lỗi permission
- Kiểm tra quyền truy cập thư mục
- Đảm bảo session được bắt đầu đúng cách

## Đóng góp

Dự án này được xây dựng cho mục đích học tập. Nếu bạn muốn đóng góp:
1. Fork repository
2. Tạo branch mới
3. Commit changes
4. Tạo Pull Request

## Giấy phép

Dự án này được phát triển cho mục đích giáo dục và có thể được sử dụng tự do.

## Liên hệ

Nếu có câu hỏi hoặc cần hỗ trợ, vui lòng liên hệ qua email: info@homestay.com

---
**Lưu ý**: Đây là phiên bản phát triển, không sử dụng mật khẩu mã hóa cho mục đích demo.