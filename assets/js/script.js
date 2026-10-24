/**
 * ============================================================
 * JAVASCRIPT PERPUSTAKAAN DIGITAL (TEMA FLORA)
 * FILE: assets/js/script.js
 * FUNGSI: Interaktivitas Client-Side, Filter Pencarian Live, 
 *         Validasi Tanggal Pinjam/Kembali, Modal & Konfirmasi
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", function () {
  // 1. Toggle Menu Navigasi di Mobile
  const toggleBtn = document.getElementById("navToggle");
  const navMenu = document.getElementById("navMenu");
  if (toggleBtn && navMenu) {
    toggleBtn.addEventListener("click", function () {
      navMenu.classList.toggle("open");
    });
  }

  // 2. Auto-close Alert setelah 4 detik
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = "opacity 0.5s ease";
      alert.style.opacity = "0";
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });

  // 3. Live Search Tabel (Pencarian Instan pada Tabel)
  const searchInput = document.getElementById("tableSearch");
  if (searchInput) {
    searchInput.addEventListener("keyup", function () {
      const keyword = searchInput.value.toLowerCase();
      const tableRows = document.querySelectorAll(".live-table tbody tr");

      tableRows.forEach(function (row) {
        const text = row.textContent.toLowerCase();
        if (text.includes(keyword)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    });
  }

  // 4. Live Search Katalog Buku (Tampilan Card Grid)
  const bookSearchInput = document.getElementById("bookCatalogSearch");
  if (bookSearchInput) {
    bookSearchInput.addEventListener("keyup", function () {
      const keyword = bookSearchInput.value.toLowerCase();
      const bookCards = document.querySelectorAll(".book-grid .book-card");

      bookCards.forEach(function (card) {
        const text = card.textContent.toLowerCase();
        if (text.includes(keyword)) {
          card.style.display = "flex";
        } else {
          card.style.display = "none";
        }
      });
    });
  }

  // 5. Otomatis Hitung Tanggal Jatuh Tempo (7 Hari dari Tanggal Pinjam)
  const tglPinjamInput = document.getElementById("tgl_pinjam");
  const tglJatuhTempoInput = document.getElementById("tgl_jatuh_tempo");
  if (tglPinjamInput && tglJatuhTempoInput) {
    tglPinjamInput.addEventListener("change", function () {
      if (tglPinjamInput.value) {
        const pinjamDate = new Date(tglPinjamInput.value);
        // Tambahkan 7 hari
        pinjamDate.setDate(pinjamDate.getDate() + 7);

        const yyyy = pinjamDate.getFullYear();
        const mm = String(pinjamDate.getMonth() + 1).padStart(2, "0");
        const dd = String(pinjamDate.getDate()).padStart(2, "0");

        tglJatuhTempoInput.value = `${yyyy}-${mm}-${dd}`;
      }
    });
  }
});

/**
 * Konfirmasi Hapus Data dengan Dialog Ramah
 * @param {string} url - Link tujuan hapus data
 * @param {string} namaData - Nama item yang akan dihapus
 */
function konfirmasiHapus(url, namaData) {
  const konfirmasi = confirm(
    `Konfirmasi Hapus\nApakah Anda yakin ingin menghapus data: "${namaData}"?\nData yang dihapus tidak dapat dikembalikan.`
  );
  if (konfirmasi) {
    window.location.href = url;
  }
}

/**
 * Modal Helper: Buka Modal
 */
function bukaModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("show");
  }
}

/**
 * Modal Helper: Tutup Modal
 */
function tutupModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove("show");
  }
}
