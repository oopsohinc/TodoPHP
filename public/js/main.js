// alerts auto-hide and stacking

document.addEventListener('DOMContentLoaded', function() {
    // 1. Tìm tất cả các phần tử có class 'alert'
    const alerts = document.querySelectorAll('.alert');

    // 2. Nếu có thông báo
    if (alerts.length > 0) {
        // Đợi 3000ms (3 giây)
        setTimeout(function() {
            alerts.forEach(function(alert) {
                // Thêm class 'hide' để kích hoạt CSS transition (mờ dần)
                alert.classList.add('hide');

                // Đợi thêm 500ms cho hiệu ứng mờ chạy xong rồi mới xóa hẳn khỏi DOM
                setTimeout(function() {
                    alert.remove();
                }, 500); 
            });
        }, 3000); // <-- Bạn có thể chỉnh sửa 3000 thành 5000 nếu muốn 5 giây
    }
});