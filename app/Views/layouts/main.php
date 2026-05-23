<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Penomoran Dokumen') ?></title>
    <link rel="icon" type="image/png" href="/images/logo-pmk.png">

    <!-- Google Fonts - Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <!-- Custom CSS (consolidated) -->
    <link href="/css/penomoran-custom.css" rel="stylesheet">
    
    <?= $this->renderSection('css') ?>
</head>
<body style="background-color: white;" class="d-flex flex-column min-vh-100">
    <?= view('partials/navbar_smart') ?>
    
    <main class="container py-4 flex-grow-1">
        <?= $this->renderSection('content') ?>
    </main>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <?php if (session()->has('success')): ?>
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i> <?= session('success') ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif ?>

        <?php if (session()->has('error')): ?>
            <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= session('error') ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif ?>
    </div>

    <?= view('partials/footer') ?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    
    <!-- Toast Initialization & Date Display -->
    <script>
        // Date Display
        function updateDate() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dayName = days[now.getDay()];
            const day = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            const dateString = `${dayName}, ${day} ${monthName} ${year}`;
            const dateElement = document.getElementById('dateDisplay');
            if (dateElement) {
                dateElement.textContent = dateString;
            }
        }
        updateDate();

        // Toast Initialization
        document.addEventListener('DOMContentLoaded', function () {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'))
            var toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl).show()
            })
        });
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
