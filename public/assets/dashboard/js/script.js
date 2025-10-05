// ============= CONSTANTS & CONFIG =============
const CONFIG = {
    ALERT_AUTO_CLOSE_DELAY: 4000,
    ALERT_ANIMATION_DURATION: 300,
    RESIZE_DEBOUNCE_DELAY: 150,
    DEBUG: false
};

const ALERT_ICONS = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle'
};

// ============= UTILITY FUNCTIONS =============
function debugLog(...args) {
    if (CONFIG.DEBUG) {
        console.log(...args);
    }
}

function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

// ============= MAIN DASHBOARD INITIALIZATION =============
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== NAVBAR DROPDOWN =====
    const profile = document.querySelector('.profile');
    const dropdownContentNavbar = profile ? profile.querySelector('.dropdown-content-navbar') : null;

    if (profile && dropdownContentNavbar) {
        profile.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownContentNavbar.classList.toggle('show');
        });
    }

    // ===== SIDEBAR TOGGLE =====
    const toggleSidebarBtn = document.querySelector('.toggle-sidebar');
    const container = document.querySelector('.container');
    const overlay = document.querySelector('.overlay');
    const sidebar = document.querySelector('.sidebar');

    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (window.innerWidth <= 768) {
                if (sidebar) sidebar.classList.toggle('show');
                if (overlay) overlay.classList.toggle('show');
            } else {
                if (container) container.classList.toggle('sidebar-collapsed');
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar) sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // ===== SIDEBAR DROPDOWN FUNCTIONS =====
    const closeAllDropdowns = (exceptDropdown = null) => {
        const allDropdowns = document.querySelectorAll('.sidebar .dropdown');
        allDropdowns.forEach(dropdown => {
            if (dropdown !== exceptDropdown) {
                const dropdownContent = dropdown.querySelector('.dropdown-content');
                const dropdownIcon = dropdown.querySelector('.dropdown-icon');

                dropdown.classList.remove('active');
                if (dropdownIcon) {
                    dropdownIcon.classList.remove('rotate');
                }
                if (dropdownContent) {
                    dropdownContent.style.maxHeight = null;
                }
            }
        });
    };

    // ===== SIDEBAR DROPDOWN MENU =====
    const dropdowns = document.querySelectorAll('.sidebar .dropdown');

    dropdowns.forEach(dropdown => {
        const dropdownHeader = dropdown.querySelector('.dropdown-header');
        const dropdownContent = dropdown.querySelector('.dropdown-content');
        const dropdownIcon = dropdown.querySelector('.dropdown-icon');

        if (dropdownHeader) {
            dropdownHeader.addEventListener('click', function(e) {
                e.stopPropagation();

                if (dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                    if (dropdownIcon) {
                        dropdownIcon.classList.remove('rotate');
                    }
                    if (dropdownContent) {
                        dropdownContent.style.maxHeight = null;
                    }
                } else {
                    closeAllDropdowns(dropdown);
                    dropdown.classList.add('active');
                    if (dropdownIcon) {
                        dropdownIcon.classList.add('rotate');
                    }
                    if (dropdownContent) {
                        dropdownContent.style.maxHeight = dropdownContent.scrollHeight + "px";
                    }
                }
            });
        }
    });

    // ===== MENU ITEMS HANDLER =====
    const menuItems = document.querySelectorAll('.sidebar .menu-item:not(.dropdown)');

    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            closeAllDropdowns();

            menuItems.forEach(menuItem => {
                menuItem.classList.remove('active');
            });

            this.classList.add('active');
        });
    });

    // ===== SUB-MENU ITEMS HANDLER =====
    const subMenuItems = document.querySelectorAll('.sidebar .sub-menu-item');

    subMenuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();

            subMenuItems.forEach(subItem => {
                subItem.classList.remove('active');
            });

            this.classList.add('active');
        });
    });

    // ===== NOTIFICATION HANDLER =====
    const notificationBtn = document.querySelector('.notification');

    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            showAlert('info', 'Notifikasi', 'Anda memiliki 3 pemberitahuan baru');
        });
    }

    // ===== CHARTS SETUP =====
    initializeCharts();

    // ===== INITIALIZE ALERTS ===== ✅ TAMBAHAN PENTING
    initializeAlerts();

    // ===== RESPONSIVE HANDLER WITH DEBOUNCE =====
    const handleResize = debounce(function() {
        if (window.innerWidth <= 768) {
            if (container) {
                container.classList.remove('sidebar-collapsed');
            }
            if (sidebar && sidebar.classList.contains('show') && overlay) {
                overlay.classList.add('show');
            }
        } else {
            if (overlay) {
                overlay.classList.remove('show');
            }
        }
    }, CONFIG.RESIZE_DEBOUNCE_DELAY);

    window.addEventListener('resize', handleResize);

    // ===== GLOBAL CLICK HANDLER =====
    document.addEventListener('click', function(e) {
        const clickedElement = e.target;
        
        // Jangan close apa-apa jika click di dalam modal
        if (clickedElement.closest('.modal')) {
            return;
        }
        
        // Close navbar dropdown jika click di luar profile
        if (dropdownContentNavbar && !clickedElement.closest('.profile')) {
            dropdownContentNavbar.classList.remove('show');
        }
        
        // Close sidebar dropdown jika click di luar sidebar
        if (!clickedElement.closest('.sidebar')) {
            closeAllDropdowns();
        }
    });

    debugLog('Dashboard JavaScript initialized successfully');
});

// ============= CHARTS INITIALIZATION =============
function initializeCharts() {
    // Monthly Activity Chart
    const monthlyChartElement = document.getElementById('monthlyActivityChart');
    if (monthlyChartElement) {
        const activityChartCtx = monthlyChartElement.getContext('2d');
        new Chart(activityChartCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Aktivitas 2025',
                    data: [65, 59, 80, 81, 56, 55, 40, 45, 60, 70, 75, 80],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // User Distribution Chart
    const userChartElement = document.getElementById('userDistributionChart');
    if (userChartElement) {
        const userChartCtx = userChartElement.getContext('2d');
        new Chart(userChartCtx, {
            type: 'pie',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    label: 'Distribusi Pengguna',
                    data: [45, 40, 15],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 206, 86, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Hide chart placeholders
    const chartPlaceholders = document.querySelectorAll('.chart-placeholder');
    chartPlaceholders.forEach(placeholder => {
        placeholder.style.display = 'none';
    });
}

// ============= ALERT SYSTEM =============
function initializeAlerts() {
    const alerts = document.querySelectorAll('.modern-alert');
    debugLog('Found alerts:', alerts.length);
    
    alerts.forEach((alert, index) => {
        debugLog(`Initializing alert ${index}`);
        
        if (!alert.hasAttribute('data-initialized')) {
            setupAlertHandlers(alert);
            alert.setAttribute('data-initialized', 'true');
            
            alert._timerId = setTimeout(() => {
                debugLog(`Auto closing alert ${index}`);
                closeAlert(alert);
            }, CONFIG.ALERT_AUTO_CLOSE_DELAY);
        }
    });
}

function setupAlertHandlers(alert) {
    const closeBtn = alert.querySelector('[data-alert-close="true"]');
    if (closeBtn) {
        const handler = (e) => handleAlertClose(e, alert);
        closeBtn.addEventListener('click', handler);
        alert._closeHandler = handler;
    }
}

function handleAlertClose(e, alert) {
    e.preventDefault();
    e.stopPropagation();
    
    if (alert) {
        debugLog('Manual close triggered');
        closeAlert(alert);
    }
}

function closeAlert(alertElement) {
    // Validasi element
    if (!alertElement || !alertElement.classList) {
        debugLog('Invalid alert element');
        return;
    }
    
    // Cek apakah sudah dalam proses closing
    if (alertElement.dataset.isClosing === 'true') {
        debugLog('Alert already closing');
        return;
    }
    
    debugLog('Closing alert');
    
    // Tandai sebagai closing
    alertElement.dataset.isClosing = 'true';
    
    // Cancel timer jika ada
    if (alertElement._timerId) {
        clearTimeout(alertElement._timerId);
        delete alertElement._timerId;
    }
    
    // Remove event listener untuk mencegah memory leak
    const closeBtn = alertElement.querySelector('[data-alert-close="true"]');
    if (closeBtn && alertElement._closeHandler) {
        closeBtn.removeEventListener('click', alertElement._closeHandler);
        delete alertElement._closeHandler;
    }
    
    // Start closing animation
    alertElement.classList.add('closing');
    
    // Remove from DOM after animation
    setTimeout(() => {
        if (alertElement && alertElement.parentNode) {
            debugLog('Removing alert from DOM');
            alertElement.remove();
        }
    }, CONFIG.ALERT_ANIMATION_DURATION);
}

function showAlert(type, title, message) {
    const container = document.getElementById('alertContainer');
    if (!container) {
        console.error('Alert container not found');
        return;
    }
    
    debugLog(`Creating ${type} alert:`, title, message);
    
    const icon = ALERT_ICONS[type] || ALERT_ICONS.info;
    
    const alertHTML = `
        <div class="modern-alert alert-${type}" role="alert">
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="${icon}"></i>
                </div>
                <div class="alert-text">
                    <strong>${title}</strong>
                    <p>${message}</p>
                </div>
                <button class="alert-close" data-alert-close="true" aria-label="Close alert">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="alert-progress"></div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', alertHTML);
    const newAlert = container.lastElementChild;
    
    // Setup handlers untuk alert baru
    setupAlertHandlers(newAlert);
    newAlert.setAttribute('data-initialized', 'true');
    
    // Auto close timer
    newAlert._timerId = setTimeout(() => {
        debugLog('Auto closing new alert');
        closeAlert(newAlert);
    }, CONFIG.ALERT_AUTO_CLOSE_DELAY);
}