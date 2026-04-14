    </div><!-- /adm-content -->
</div><!-- /adm-main -->

    <script>
        // Auto-expand textarea (no scrollbar)
        document.querySelectorAll('.adm-textarea, .form-control').forEach(el => {
            if(el.tagName.toLowerCase() !== 'textarea') return;
            
            const resize = () => {
                el.style.height = 'min-content';
                el.style.height = el.scrollHeight + 'px';
            };
            
            // Handle existing data on page load
            el.style.overflowY = 'hidden';
            el.style.resize = 'none'; // Disable manual resize
            
            // Small timeout to ensure DOM is rendered before computing height
            setTimeout(resize, 0); 
            
            // Handle user input
            el.addEventListener('input', resize);
        });
    </script>

    <!-- SweetAlert2 for beautiful confirmation dialogs -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const msg = this.dataset.msg || 'Are you sure you want to delete this item? This action cannot be undone.';
                Swal.fire({
                    title: 'Confirm Delete',
                    text: msg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-trash"></i> Yes, delete',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'adm-card',
                        confirmButton: 'btn btn-dark',
                        cancelButton: 'btn btn-outline',
                        title: 'swal-custom-title'
                    },
                    buttonsStyling: false // Use project's custom button styles instead of SweetAlert defaults
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
