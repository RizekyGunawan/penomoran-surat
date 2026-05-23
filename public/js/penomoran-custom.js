// Penomoran Surat Custom JavaScript

// Search functionality with debounce
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");

  if (searchInput) {
    let searchTimeout;

    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        performSearch(this.value);
      }, 500);
    });
  }

  // Initialize filter values from URL
  // Initialize filter values from URL
  initializeFilters();

  // Render active filter pills
  renderActiveFilters();
});

function performSearch(keyword) {
  const url = new URL(window.location.href);
  if (keyword) {
    url.searchParams.set("search", keyword);
  } else {
    url.searchParams.delete("search");
  }
  url.searchParams.set("page", 1);
  window.location.href = url.toString();
}

// Filter Modal Functions
function toggleFilterModal() {
  const modal = document.getElementById("filterModal");
  if (modal) {
    modal.classList.toggle("show");
  }
}

function closeFilter() {
  const modal = document.getElementById("filterModal");
  if (modal) {
    modal.classList.remove("show");
  }
}

function applyFilters() {
  const jenis = document.getElementById("filterJenis").value;
  const startDate = document.getElementById("filterStartDate").value;
  const endDate = document.getElementById("filterEndDate").value;
  const unitKerja = document.getElementById("filterUnitKerja").value;

  const url = new URL(window.location.href);

  if (jenis) {
    url.searchParams.set("jenis", jenis);
  } else {
    url.searchParams.delete("jenis");
  }

  if (startDate) {
    url.searchParams.set("start_date", startDate);
  } else {
    url.searchParams.delete("start_date");
  }

  if (endDate) {
    url.searchParams.set("end_date", endDate);
  } else {
    url.searchParams.delete("end_date");
  }

  if (unitKerja) {
    url.searchParams.set("unit_kerja", unitKerja);
  } else {
    url.searchParams.delete("unit_kerja");
  }

  // Clear legacy filters if they exist
  url.searchParams.delete("tahun");

  url.searchParams.set("page", 1);
  window.location.href = url.toString();
}

function initializeFilters() {
  const url = new URL(window.location.href);
  const jenis = url.searchParams.get("jenis");
  const startDate = url.searchParams.get("start_date");
  const endDate = url.searchParams.get("end_date");
  const unitKerja = url.searchParams.get("unit_kerja");

  const filterJenis = document.getElementById("filterJenis");
  const filterStartDate = document.getElementById("filterStartDate");
  const filterEndDate = document.getElementById("filterEndDate");
  const filterUnitKerja = document.getElementById("filterUnitKerja");

  if (filterJenis && jenis) {
    filterJenis.value = jenis;
  }
  if (filterStartDate && startDate) {
    filterStartDate.value = startDate;
  }
  if (filterEndDate && endDate) {
    filterEndDate.value = endDate;
  }
  if (filterUnitKerja && unitKerja) {
    filterUnitKerja.value = unitKerja;
  }
}

// Filter by Status (for statistic cards)
function filterByStatus(status) {
  const url = new URL(window.location.href);

  if (status === "SEMUA") {
    // Remove status filter to show all
    url.searchParams.delete("status");
  } else {
    // Set status filter (DITERBITKAN or DIBATALKAN)
    url.searchParams.set("status", status);
  }

  // Reset to page 1 when filter changes
  url.searchParams.set("page", 1);
  window.location.href = url.toString();
}

// Pagination Functions
function changePerPage(perPage) {
  const url = new URL(window.location.href);
  url.searchParams.set("per_page", perPage);
  url.searchParams.set("page", 1); // Reset to page 1
  window.location.href = url.toString();
}

function goToPage(page) {
  const url = new URL(window.location.href);
  url.searchParams.set("page", page);
  window.location.href = url.toString();
}

// Close dropdown when clicking outside
// Close modal when clicking on the backdrop
// Close dropdown when clicking outside
document.addEventListener("click", function (event) {
  const filterSection = document.getElementById("filterModal");
  const filterBtn = document.getElementById("filterBtn");

  // If filter is currently shown
  if (filterSection && filterSection.classList.contains("show")) {
    // If the click is NOT inside the filter content AND NOT on the button/icon
    if (
      !filterSection.contains(event.target) &&
      !filterBtn.contains(event.target)
    ) {
      closeFilter();
    }
  }
});

// Cancel Modal Functions
function openCancelModal(no, jenis, tanggal, tahun, nomorLengkap) {
  // Set hidden values
  document.getElementById("cancelInputNo").value = no;
  document.getElementById("cancelInputJenis").value = jenis;
  document.getElementById("cancelInputTahun").value = tahun;

  // Format Date
  const dateStr = formatDate(tanggal);

  // Populate Text Fields (New Layout)
  const noText = document.getElementById("cancelShowNoText");
  if (noText) noText.textContent = nomorLengkap || no;

  const jenisText = document.getElementById("cancelShowJenisText");
  if (jenisText) jenisText.textContent = jenis;

  const tanggalText = document.getElementById("cancelShowTanggalText");
  if (tanggalText) tanggalText.textContent = dateStr;

  // Fallback: Populate Inputs (Old Layout - if any)
  const noInput = document.getElementById("cancelShowNo");
  if (noInput) noInput.value = no;

  const jenisInput = document.getElementById("cancelShowJenis");
  if (jenisInput) jenisInput.value = jenis;

  const tanggalInput = document.getElementById("cancelShowTanggal");
  if (tanggalInput) tanggalInput.value = dateStr;

  // Clear textarea
  document.getElementById("ALASAN_PEMBATALAN").value = "";

  // Show Modal
  const modal = new bootstrap.Modal(document.getElementById("cancelModal"));
  modal.show();
}

function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  const months = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];
  return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
}

// Active Filter Functions
function renderActiveFilters() {
  const container = document.getElementById("activeFilters");
  if (!container) return;

  const url = new URL(window.location.href);
  const params = url.searchParams;

  const filters = [];

  // Check for specific filters
  if (params.get("start_date") && params.get("end_date")) {
    const start = formatDate(params.get("start_date"));
    const end = formatDate(params.get("end_date"));
    filters.push({
      label: `${start} - ${end}`,
      keys: ["start_date", "end_date"],
    });
  } else if (params.get("start_date")) {
    // Handle single date case just in case
    const start = formatDate(params.get("start_date"));
    filters.push({
      label: `Dari: ${start}`,
      keys: ["start_date"],
    });
  } else if (params.get("end_date")) {
    const end = formatDate(params.get("end_date"));
    filters.push({
      label: `Sampai: ${end}`,
      keys: ["end_date"],
    });
  }

  if (params.get("jenis")) {
    filters.push({
      label: params.get("jenis"),
      keys: ["jenis"],
    });
  }

  if (params.get("unit_kerja")) {
    filters.push({
      label: params.get("unit_kerja"),
      keys: ["unit_kerja"],
    });
  }

  // Clear container
  container.innerHTML = "";

  if (filters.length > 0) {
    filters.forEach((filter) => {
      const pill = document.createElement("div");
      pill.className = "filter-pill";
      // Escape label to prevent XSS
      const safeLabel = filter.label
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

      // JSON stringify keys for the onclick handler
      const keysString = JSON.stringify(filter.keys).replace(/"/g, "&quot;");

      pill.innerHTML = `
        <span>${safeLabel}</span>
        <i class="fas fa-times remove-filter" onclick="removeFilters(${keysString})"></i>
      `;
      container.appendChild(pill);
    });

    // Add Clear All button
    const clearBtn = document.createElement("button");
    clearBtn.className = "btn-clear-filters";
    clearBtn.textContent = "Hapus Semua Filter";
    clearBtn.onclick = clearAllFilters;
    container.appendChild(clearBtn);
  }
}

function removeFilters(keys) {
  const url = new URL(window.location.href);
  if (Array.isArray(keys)) {
    keys.forEach((key) => url.searchParams.delete(key));
  }
  url.searchParams.set("page", 1);
  window.location.href = url.toString();
}

// Clear all filters
function clearAllFilters() {
  const url = new URL(window.location.href);
  ["jenis", "start_date", "end_date", "unit_kerja", "tahun"].forEach((key) =>
    url.searchParams.delete(key),
  );
  url.searchParams.set("page", 1);
  window.location.href = url.toString();
}

// Copy to Clipboard with Snackbar
function copyToClipboard(text) {
  // Create snackbar element if it doesn't exist
  let snackbar = document.getElementById("snackbar");
  if (!snackbar) {
    snackbar = document.createElement("div");
    snackbar.id = "snackbar";
    document.body.appendChild(snackbar);
  }

  // Copy text
  navigator.clipboard
    .writeText(text)
    .then(() => {
      // Show snackbar
      snackbar.textContent = "Nomor surat berhasil disalin!";
      snackbar.className = "show";

      // Hide after 3 seconds
      setTimeout(function () {
        snackbar.className = snackbar.className.replace("show", "");
      }, 3000);
    })
    .catch((err) => {
      console.error("Gagal menyalin: ", err);
      alert("Gagal menyalin nomor surat");
    });
}
