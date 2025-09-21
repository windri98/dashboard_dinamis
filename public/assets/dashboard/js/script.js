// Fixed Dashboard JavaScript - Non-conflicting version
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
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            } else {
                container.classList.toggle('sidebar-collapsed');
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.remove('show');
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
            alert('Notifikasi: Anda memiliki 3 pemberitahuan baru');
        });
    }

    // ===== CHARTS SETUP =====
    // Monthly Activity Chart
    if (document.getElementById('monthlyActivityChart')) {
        const activityChartCtx = document.getElementById('monthlyActivityChart').getContext('2d');
        const activityChart = new Chart(activityChartCtx, {
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

        const chartPlaceholder = document.querySelector('.chart-placeholder');
        if (chartPlaceholder) {
            chartPlaceholder.style.display = 'none';
        }
    }

    // User Distribution Chart
    if (document.getElementById('userDistributionChart')) {
        const userChartCtx = document.getElementById('userDistributionChart').getContext('2d');
        const userChart = new Chart(userChartCtx, {
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

        const chartPlaceholders = document.querySelectorAll('.chart-placeholder');
        if (chartPlaceholders[1]) {
            chartPlaceholders[1].style.display = 'none';
        }
    }

    // ===== RESPONSIVE HANDLER =====
    window.addEventListener('resize', function() {
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
    });

    // ===== GLOBAL CLICK HANDLER (IMPROVED) =====
    // Handle clicks outside specific elements
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

    console.log('Dashboard JavaScript initialized successfully');
});