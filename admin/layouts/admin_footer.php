    </div><!-- /adm-content -->
</div><!-- /adm-main -->

    <script>
        // Tự động mở rộng Textarea (Không có thanh cuộn)
        document.querySelectorAll('.adm-textarea, .form-control').forEach(el => {
            if(el.tagName.toLowerCase() !== 'textarea') return;
            
            const resize = () => {
                el.style.height = 'min-content';
                el.style.height = el.scrollHeight + 'px';
            };
            
            // Xử lý khi trang vừa load xong có sẵn data
            el.style.overflowY = 'hidden';
            el.style.resize = 'none'; // Không cho kéo thủ công
            
            // Timeout nhỏ để đảm bảo render DOM xong mới lấy height chuẩn
            setTimeout(resize, 0); 
            
            // Xử lý khi user gõ phím
            el.addEventListener('input', resize);
        });
    </script>

    <!-- SweetAlert2 cho các hộp thoại xác nhận đẹp mắt -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const msg = this.dataset.msg || 'Bạn có chắc chắn muốn xoá mục này không? Hành động này không thể hoàn tác.';
                Swal.fire({
                    title: 'Xác nhận xoá',
                    text: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-trash"></i> Đồng ý xoá',
                    cancelButtonText: 'Hủy bỏ',
                    customClass: {
                        popup: 'adm-card',
                        confirmButton: 'btn btn-dark',
                        cancelButton: 'btn btn-outline',
                        title: 'swal-custom-title'
                    },
                    buttonsStyling: false // Loại bỏ style mặc định của thư viện để dùng style của dự án
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
    <style>
        .swal2-container { font-family: 'Inter', sans-serif !important; }
        .swal-custom-title { font-family: 'Inter', sans-serif !important; font-size: 20px !important; font-weight: 600; }
        .swal2-popup.adm-card { border: 1px solid var(--gray-300); padding: 32px; border-radius: 12px; }
        .swal2-actions { margin-top: 24px !important; gap: 12px; }
        div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel), div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm) { border: none !important; }
    </style>
</body>
</html>
