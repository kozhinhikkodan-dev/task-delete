@if (session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (window.Toastify) {
                Toastify({
                    text: "{{ session('success') }}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    className: "bg-success",
                    stopOnFocus: true
                }).showToast();
            }
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (window.Toastify) {
                Toastify({
                    text: "{{ session('error') }}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    className: "bg-danger",
                    stopOnFocus: true
                }).showToast();
            }
        });
    </script>
@endif

