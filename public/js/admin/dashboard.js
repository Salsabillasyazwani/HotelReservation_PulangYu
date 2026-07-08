// Dashboard content scripts (sidebar & navbar scripts NOT included)

// Toast Notification System
function showToast(title, message, type = 'success') {
  const toast = document.getElementById('toast');
  const toastIcon = document.getElementById('toastIcon');
  const toastTitle = document.getElementById('toastTitle');
  const toastMessage = document.getElementById('toastMessage');

  toastTitle.textContent = title;
  toastMessage.textContent = message;

  if (type === 'success') {
    toastIcon.className = 'w-8 h-8 rounded-xl bg-green-500/20 flex items-center justify-center';
    toastIcon.innerHTML = '<i class="fas fa-check text-green-400 text-sm"></i>';
  } else if (type === 'info') {
    toastIcon.className = 'w-8 h-8 rounded-xl bg-blue-500/20 flex items-center justify-center';
    toastIcon.innerHTML = '<i class="fas fa-info text-blue-400 text-sm"></i>';
  } else if (type === 'warning') {
    toastIcon.className = 'w-8 h-8 rounded-xl bg-amber-500/20 flex items-center justify-center';
    toastIcon.innerHTML = '<i class="fas fa-tag text-amber-400 text-sm"></i>';
  }

  toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
  toast.classList.add('translate-y-0', 'opacity-100');

  setTimeout(() => {
    toast.classList.remove('translate-y-0', 'opacity-100');
    toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
  }, 3000);
}

// Use Promo
function usePromo(code) {
  showToast('Promo Applied!', `${code} has been applied to your account`, 'warning');
}

// Book Now
function bookNow(roomName, price) {
  document.getElementById('modalRoomName').textContent = roomName;
  document.getElementById('modalPrice').textContent = price || 'Rp 750.000';

  const roomInput = document.getElementById('modalRoomInput');
  if (roomInput) roomInput.value = roomName;

  const modal = document.getElementById('bookingModal');
  const content = document.getElementById('bookingModalContent');
  modal.classList.remove('hidden');
  setTimeout(() => {
    content.classList.remove('scale-95', 'opacity-0');
    content.classList.add('scale-100', 'opacity-100');
  }, 50);
}

function closeBookingModal() {
  const modal = document.getElementById('bookingModal');
  const content = document.getElementById('bookingModalContent');
  content.classList.remove('scale-100', 'opacity-100');
  content.classList.add('scale-95', 'opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
}

// Toggle Favorite
function toggleFavorite(btn, e) {
  e.stopPropagation();
  const icon = btn.querySelector('i');
  if (icon.classList.contains('far')) {
    icon.classList.remove('far', 'text-gray-400');
    icon.classList.add('fas', 'text-red-500');
    icon.classList.add('badge-pulse');
    setTimeout(() => icon.classList.remove('badge-pulse'), 500);
    showToast('Saved to Wishlist', 'Room added to your favorites', 'info');
  } else {
    icon.classList.remove('fas', 'text-red-500');
    icon.classList.add('far', 'text-gray-400');
    showToast('Removed from Wishlist', 'Room removed from favorites', 'info');
  }
}

// Scroll rooms horizontally
function scrollRooms(dir) {
  const grid = document.getElementById('roomsGrid');
  grid.scrollBy({ left: dir * 300, behavior: 'smooth' });
}

// Animate stat numbers on load
function animateNumbers() {
  document.querySelectorAll('.card-hover h3').forEach(el => {
    const target = parseInt(el.textContent);
    if (isNaN(target)) return;
    let current = 0;
    const step = Math.ceil(target / 30);
    const timer = setInterval(() => {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = current;
    }, 40);
  });
}

// Intersection Observer for scroll-in animations
const dashboardObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('animate-fade-in-up');
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.card-hover, .promo-card, .room-card').forEach(el => {
  dashboardObserver.observe(el);
});

// Escape key closes booking modal
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeBookingModal();
  }
});

// ============================================================
// CHART RENDERING (uses window.dashboardData set in blade file)
// Requires Chart.js to be loaded BEFORE this script, e.g.:
// <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
// ============================================================

function renderDashboardCharts() {
  if (typeof Chart === 'undefined') {
    console.error('Chart.js is not loaded. Make sure the Chart.js <script> tag is included before dashboard.js');
    return;
  }

  if (!window.dashboardData) {
    console.error('window.dashboardData is not defined. Make sure it is set in the blade view before dashboard.js loads.');
    return;
  }

  const { reservationChart, revenueChart, occupancyRate, occupiedRoom, totalRoom } = window.dashboardData;

  // ---- Reservation Overview (Line Chart) ----
  const reservationCanvas = document.getElementById('reservationChart');
  if (reservationCanvas && reservationChart) {
    new Chart(reservationCanvas, {
      type: 'line',
      data: {
        labels: reservationChart.labels,
        datasets: [{
          label: 'Reservation',
          data: reservationChart.data,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointRadius: 3,
          pointBackgroundColor: '#3b82f6'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 },
            grid: { color: 'rgba(0,0,0,0.05)' }
          },
          x: {
            grid: { display: false }
          }
        }
      }
    });
  }

  // ---- Revenue Overview (Bar Chart) ----
  const revenueCanvas = document.getElementById('revenueChart');
  if (revenueCanvas && revenueChart) {
    new Chart(revenueCanvas, {
      type: 'bar',
      data: {
        labels: revenueChart.labels,
        datasets: [{
          label: 'Revenue',
          data: revenueChart.data,
          backgroundColor: '#22c55e',
          borderRadius: 6,
          maxBarThickness: 28
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => 'Rp ' + Number(ctx.raw).toLocaleString('id-ID')
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (val) => 'Rp ' + Number(val).toLocaleString('id-ID')
            },
            grid: { color: 'rgba(0,0,0,0.05)' }
          },
          x: {
            grid: { display: false }
          }
        }
      }
    });
  }

  // ---- Occupancy Rate (Doughnut Chart) ----
  const occupancyCanvas = document.getElementById('occupancyChart');
  if (occupancyCanvas) {
    const occupied = occupiedRoom || 0;
    const total = totalRoom || 0;
    const vacant = Math.max(total - occupied, 0);

    new Chart(occupancyCanvas, {
      type: 'doughnut',
      data: {
        labels: ['Occupied', 'Vacant'],
        datasets: [{
          data: [occupied, vacant],
          backgroundColor: ['#22c55e', '#e5e7eb'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true }
        }
      }
    });
  }
}

// Run animations and charts on load
window.addEventListener('load', () => {
  animateNumbers();
  renderDashboardCharts();
});
