    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-home"></i> Homestay</h5>
                    <p>Hệ thống quản lý homestay chuyên nghiệp, giúp bạn dễ dàng quản lý và đặt phòng.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light">Trang chủ</a></li>
                        <li><a href="auth/login.php" class="text-light">Đăng nhập</a></li>
                        <li><a href="auth/register.php" class="text-light">Đăng ký</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, Quận 1, TP.HCM</li>
                        <li><i class="fas fa-phone"></i> 0123 456 789</li>
                        <li><i class="fas fa-envelope"></i> info@homestay.com</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; 2024 Homestay Management System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Date validation for booking forms
        function validateDates() {
            var checkIn = document.getElementById('check_in_date');
            var checkOut = document.getElementById('check_out_date');
            
            if (checkIn && checkOut) {
                var today = new Date().toISOString().split('T')[0];
                checkIn.min = today;
                
                checkIn.addEventListener('change', function() {
                    checkOut.min = this.value;
                    if (checkOut.value && checkOut.value < this.value) {
                        checkOut.value = '';
                    }
                });
            }
        }

        // Initialize date validation
        validateDates();
    </script>
</body>
</html>