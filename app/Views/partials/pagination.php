<?php
/**
 * Partial: Pagination
 *
 * Digunakan di: Views/penomoran/index.php, Views/penomoran/admin.php
 *
 * Variabel yang dibutuhkan dari controller (via $pagination array):
 * @var array $pagination {
 *   int $per_page       Jumlah item per halaman
 *   int $total_records  Total semua record
 *   int $current_page   Halaman saat ini
 *   int $total_pages    Total halaman
 * }
 */
?>
<div class="pagination-container">
    <!-- Kiri: Pemilih Per Halaman -->
    <div class="per-page-selector">
        <select class="per-page-dropdown" onchange="changePerPage(this.value)">
            <option value="10"  <?= $pagination['per_page'] == 10  ? 'selected' : '' ?>>10</option>
            <option value="25"  <?= $pagination['per_page'] == 25  ? 'selected' : '' ?>>25</option>
            <option value="50"  <?= $pagination['per_page'] == 50  ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $pagination['per_page'] == 100 ? 'selected' : '' ?>>100</option>
        </select>
        <span class="pagination-info-text">
            Menampilkan <?= $pagination['per_page'] ?> dari <?= $pagination['total_records'] ?>
        </span>
    </div>

    <!-- Kanan: Pagination Bernomor -->
    <div class="numbered-pagination">
        <!-- Panah Sebelumnya -->
        <button class="page-arrow"
                onclick="goToPage(<?= $pagination['current_page'] - 1 ?>)"
                <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>>
            <i class="fas fa-chevron-left"></i>
        </button>

        <?php
        $currentPage = $pagination['current_page'];
        $totalPages  = $pagination['total_pages'];

        // Tampilkan halaman pertama
        if ($currentPage > 2) {
            echo '<button class="page-btn" onclick="goToPage(1)">1</button>';
            if ($currentPage > 3) {
                echo '<span class="page-ellipsis">...</span>';
            }
        }

        // Tampilkan halaman sekitar saat ini
        for ($i = max(1, $currentPage - 1); $i <= min($totalPages, $currentPage + 1); $i++) {
            $activeClass = $i == $currentPage ? 'active' : '';
            echo "<button class='page-btn {$activeClass}' onclick='goToPage({$i})'>{$i}</button>";
        }

        // Tampilkan halaman terakhir
        if ($currentPage < $totalPages - 1) {
            if ($currentPage < $totalPages - 2) {
                echo '<span class="page-ellipsis">...</span>';
            }
            echo "<button class='page-btn' onclick='goToPage({$totalPages})'>{$totalPages}</button>";
        }
        ?>

        <!-- Panah Berikutnya -->
        <button class="page-arrow"
                onclick="goToPage(<?= $pagination['current_page'] + 1 ?>)"
                <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
