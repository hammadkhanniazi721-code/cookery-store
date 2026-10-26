// ============================================
// ADMIN PANEL JAVASCRIPT
// ============================================

// ============================================
// SIDEBAR TOGGLE (Mobile)
// ============================================
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// ============================================
// CLOSE SIDEBAR WHEN CLICKING OUTSIDE (Mobile)
// ============================================
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('adminSidebar');
    const toggle = e.target.closest('.menu-toggle');

    if (!sidebar) return;

    if (window.innerWidth <= 992 && !toggle && !sidebar.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});

// ============================================
// CONFIRM DELETE
// ============================================
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this?');
}

// ============================================
// AUTO-HIDE ALERTS (3 seconds baad)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-hide');

    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';

            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 3000);
    });
});

// ============================================
// OPEN / CLOSE MODAL
// ============================================
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});

// ============================================
// TABLE SEARCH (Live filter)
// ============================================
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);

    if (!input || !table) return;

    const filter = input.value.toLowerCase();
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;

        if (text.toLowerCase().indexOf(filter) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}