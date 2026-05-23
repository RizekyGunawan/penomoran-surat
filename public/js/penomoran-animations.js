/**
 * Penomoran Animations - GPU Accelerated
 *
 * Features:
 * - Counter animation untuk statistics
 * - Table row stagger effect
 * - Ripple effect untuk buttons
 * - Intersection Observer untuk lazy animations
 */

(function () {
  "use strict";

  // ============================================
  // 1. Counter Animation
  // ============================================

  function animateCounter(element, target, duration = 1500) {
    const start = 0;
    const startTime = performance.now();

    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);

      // Easing function (ease-out cubic)
      const easeOut = 1 - Math.pow(1 - progress, 3);
      const current = Math.floor(start + (target - start) * easeOut);

      element.textContent = current.toLocaleString("id-ID");

      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        element.textContent = target.toLocaleString("id-ID");
      }
    }

    requestAnimationFrame(update);
  }

  // Initialize counters dengan Intersection Observer
  function initCounters() {
    const counters = document.querySelectorAll(".stat-number");
    const observerOptions = {
      threshold: 0.5,
      rootMargin: "0px",
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && !entry.target.dataset.animated) {
          // Baca nilai dari data-value (aman) bukan textContent (bisa kosong saat observer dipanggil awal)
          const target = parseInt(entry.target.dataset.value ?? "0", 10);
          if (!isNaN(target)) {
            entry.target.dataset.animated = "true";
            animateCounter(entry.target, target, 1200);
          }
        }
      });
    }, observerOptions);

    counters.forEach((counter) => {
      counter.classList.add("counter");
      observer.observe(counter);
    });
  }

  // ============================================
  // 2. Table Row Stagger Animation (OPTIMIZED)
  // ============================================

  function initTableStagger() {
    const tableRows = document.querySelectorAll(".custom-table tbody tr");

    // Limit stagger animation untuk performa (max 30 rows)
    const maxStagger = Math.min(tableRows.length, 30);

    tableRows.forEach((row, index) => {
      row.classList.add("stagger-item");

      // Jika lebih dari max, langsung show tanpa delay
      if (index >= maxStagger) {
        row.classList.add("show");
        return;
      }

      // Delay yang lebih cepat: 25ms per row (dari 50ms)
      setTimeout(() => {
        row.classList.add("show");
      }, index * 25); // 25ms delay per row (OPTIMIZED)
    });
  }

  // ============================================
  // 3. Ripple Effect untuk Buttons
  // ============================================

  function createRipple(event) {
    const button = event.currentTarget;

    // Skip jika tidak ada class ripple-container
    if (!button.classList.contains("ripple-container")) {
      return;
    }

    const ripple = document.createElement("span");
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = size + "px";
    ripple.style.left = x + "px";
    ripple.style.top = y + "px";
    ripple.classList.add("ripple");

    button.appendChild(ripple);

    // Remove ripple setelah animasi selesai
    setTimeout(() => {
      ripple.remove();
    }, 600);
  }

  function initRippleEffect() {
    // Tambahkan ke semua buttons dengan class specific
    const rippleButtons = document.querySelectorAll(
      ".btn-primary, .bg-navy, .tab-btn",
    );

    rippleButtons.forEach((button) => {
      button.classList.add("ripple-container");
      button.addEventListener("click", createRipple);
    });
  }

  // ============================================
  // 4. Fade In Elements on Scroll
  // ============================================

  function initScrollAnimations() {
    const fadeElements = document.querySelectorAll(".fade-in-scroll");

    if (fadeElements.length === 0) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("fade-in");
          }
        });
      },
      {
        threshold: 0.1,
      },
    );

    fadeElements.forEach((el) => observer.observe(el));
  }

  // ============================================
  // 5. Enhanced Statistics Cards (DISABLED FOR FORMAL DESIGN)
  // ============================================

  // DISABLED: For formal government website, we avoid flashy animations
  // Stat cards are displayed immediately without fade-in effects
  function initStatCards() {
    // No animations for statistics cards to prevent flickering
    // Cards have subtle hover effects defined in CSS only
    return;
  }

  // ============================================
  // 6. Loading State Helper
  // ============================================

  window.showLoadingSkeleton = function (tableSelector) {
    const table = document.querySelector(tableSelector);
    if (!table) return;

    const tbody = table.querySelector("tbody");
    if (!tbody) return;

    const skeletonHTML = `
            <tr>
                <td colspan="100%">
                    <div class="skeleton skeleton-text" style="width: 80%; margin-bottom: 10px;"></div>
                    <div class="skeleton skeleton-text" style="width: 60%;"></div>
                </td>
            </tr>
        `.repeat(5);

    tbody.innerHTML = skeletonHTML;
  };

  window.hideLoadingSkeleton = function (tableSelector, originalContent) {
    const table = document.querySelector(tableSelector);
    if (!table) return;

    const tbody = table.querySelector("tbody");
    if (!tbody) return;

    tbody.innerHTML = originalContent;
    initTableStagger();
  };

  // ============================================
  // 7. Smooth Scroll to Element
  // ============================================

  window.smoothScrollTo = function (elementId) {
    const element = document.getElementById(elementId);
    if (element) {
      element.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  };

  // ============================================
  // 8. Modal Animation Enhancements
  // ============================================

  function initModalAnimations() {
    // Enhance Bootstrap modals dengan scale-in animation
    const modals = document.querySelectorAll(".modal");

    modals.forEach((modal) => {
      modal.addEventListener("show.bs.modal", function () {
        const dialog = this.querySelector(".modal-content");
        if (dialog) {
          dialog.classList.add("scale-in");
        }
      });

      modal.addEventListener("hidden.bs.modal", function () {
        const dialog = this.querySelector(".modal-content");
        if (dialog) {
          dialog.classList.remove("scale-in");
        }
      });
    });
  }

  // ============================================
  // 9. Page Load Animations
  // ============================================

  function initPageAnimations() {
    // DISABLED: No page load animations for formal government website
    // Elements appear immediately to prevent flickering and provide instant feedback
    return;
  }

  // ============================================
  // Initialization
  // ============================================

  document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi semua animasi setelah DOM siap
    initPageAnimations();
    initCounters();
    initTableStagger();
    initRippleEffect();
    initScrollAnimations();
    initStatCards();
    initModalAnimations();
  });

  // Re-initialize table stagger setelah AJAX/filter changes
  window.reinitTableStagger = function () {
    initTableStagger();
  };

  // Expose untuk debugging
  window.PenomoranAnimations = {
    initCounters,
    initTableStagger,
    initRippleEffect,
    initScrollAnimations,
    initStatCards,
    animateCounter,
  };
})();
