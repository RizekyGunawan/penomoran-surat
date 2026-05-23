<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Nomor Surat') ?></title>
    <link rel="icon" type="image/png" href="/images/logo-pmk.png">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/css/penomoran-custom.css" rel="stylesheet">
  </head>
  <body class="bg-light">
    <?= view('partials/navbar_smart') ?>
    <main class="container py-4">
      <?= $this->renderSection('content') ?>
    </main>
    <?= view('partials/footer') ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Date Display Script -->
    <script>
        function updateDate() {
            const now = new Date();
            
            // Array nama hari dalam Bahasa Indonesia
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            
            // Array nama bulan dalam Bahasa Indonesia
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const dayName = days[now.getDay()];
            const day = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            
            // Format: Senin, 13 Januari 2026
            const dateString = `${dayName}, ${day} ${monthName} ${year}`;
            
            const dateElement = document.getElementById('dateDisplay');
            if (dateElement) {
                dateElement.textContent = dateString;
            }
        }
        
        // Update saat halaman dimuat
        updateDate();
    </script>
  </body>
</html>
