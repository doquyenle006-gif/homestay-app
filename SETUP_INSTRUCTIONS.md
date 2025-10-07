# Setup Instructions for Homestay Management System

## Step-by-Step Guide to Run setup_database.php

### Prerequisites
1. **XAMPP Installed** - Make sure you have XAMPP installed on your computer
2. **Project Location** - The project should be in: `C:\xampp\htdocs\homestay_v2\`

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Click "Start" next to **Apache**
3. Click "Start" next to **MySQL**
4. Both services should show green indicators when running

### Step 2: Access the Setup Script
Open your web browser and navigate to:
```
http://localhost/homestay_v2/setup_database.php
```

### Step 3: What Happens Next
When you access the setup script, it will automatically:
1. ✅ Connect to MySQL database
2. ✅ Create database `homestay_db` if it doesn't exist
3. ✅ Create all necessary tables (users, rooms, bookings, cart)
4. ✅ Insert sample admin user and demo rooms
5. ✅ Display success messages for each step

### Expected Output
You should see messages like:
```
Database connected successfully!
Users table created successfully!
Rooms table created successfully!
Bookings table created successfully!
Cart table created successfully!
Admin user created successfully!
Sample rooms created successfully!
Database setup completed! You can now access the website.
```

### Step 4: Test the Application
After successful setup, you can:
1. **Visit Homepage**: `http://localhost/homestay_v2/`
2. **Login as Admin**: 
   - Go to `http://localhost/homestay_v2/auth/login.php`
   - Username: `admin`
   - Password: `admin123`
3. **Login as User**:
   - Username: `john_doe`
   - Password: `password123`

### Troubleshooting

#### If you see connection errors:
1. **Check XAMPP**: Make sure Apache and MySQL are running
2. **Check Database Credentials**: Verify in `config/database.php`:
   ```php
   $host = 'localhost';
   $username = 'root';
   $password = '';  // Default XAMPP password is empty
   $database = 'homestay_db';
   ```

#### If tables aren't created:
1. Check if you have permission to create databases
2. Try accessing phpMyAdmin at `http://localhost/phpmyadmin` to verify database creation

#### If you get permission errors:
1. Make sure the project folder has proper read/write permissions
2. Check that XAMPP is running with administrator privileges if needed

### Manual Database Setup (Alternative)
If the automatic setup doesn't work, you can:
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database manually: `homestay_db`
3. Import the SQL from `database_schema.md` file

### Verification
After setup, you should be able to:
- ✅ Access the homepage without errors
- ✅ Register new users
- ✅ Login with demo accounts
- ✅ Browse rooms and make bookings
- ✅ Access admin dashboard

### Next Steps
Once setup is complete:
1. Explore the user features
2. Test the admin functionality
3. Customize rooms and settings as needed
4. Add more users and rooms through the admin panel

## Important Notes
- The system uses plain text passwords as requested (for development/testing)
- All sample data is automatically created during setup
- The database will be ready for immediate use after setup
- You can modify room details and user information through the admin panel

If you encounter any issues during setup, please check the error messages and ensure XAMPP services are properly running.