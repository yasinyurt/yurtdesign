</main>
<footer>
</footer>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/admin.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const adminSidebar = document.getElementById('adminSidebar');
        const contentWrapper = document.getElementById('contentWrapper');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const profileDropdown = document.getElementById('profileDropdown');
        const profileDropdownMenu = document.getElementById('profileDropdownMenu');
        const menuTooltip = document.getElementById('menuTooltip');
        const menuLinks = document.querySelectorAll('.sidebar-menu ul li a[data-tooltip]');

        // OPTIMIZED Sidebar Toggle
        if (sidebarToggle && adminSidebar && contentWrapper) {
            sidebarToggle.addEventListener('click', function () {
                // Hide tooltip during transition
                if (activeTooltip) {
                    activeTooltip.classList.remove('show');
                    activeTooltip = null;
                }

                adminSidebar.classList.toggle('collapsed');
                contentWrapper.classList.toggle('full-width');
            });
        }

        // Profile Dropdown
        if (profileDropdown && profileDropdownMenu) {
            profileDropdown.addEventListener('click', function (e) {
                e.stopPropagation();
                profileDropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', function (e) {
                if (!profileDropdown.contains(e.target)) {
                    profileDropdownMenu.classList.remove('show');
                }
            });
        }

        // Aktif menü
        const currentPath = window.location.href;
        const basePath = '<?php echo BASE_URL; ?>panel/dashboard';

        menuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPath || (currentPath.startsWith(href) && href !== basePath && currentPath.includes(href.split('/').pop()))) {
                link.classList.add('active');
            }
        });

        // OPTIMIZED Tooltip sistemi
        let activeTooltip = null;
        let tooltipTimeout = null;

        menuLinks.forEach(link => {
            const tooltipText = link.getAttribute('data-tooltip');

            link.addEventListener('mouseenter', function () {
                if (adminSidebar.classList.contains('collapsed') && tooltipText) {
                    clearTimeout(tooltipTimeout);
                    const rect = link.getBoundingClientRect();
                    menuTooltip.textContent = tooltipText;
                    menuTooltip.style.top = (rect.top + rect.height / 2 - 16) + 'px';

                    tooltipTimeout = setTimeout(() => {
                        menuTooltip.classList.add('show');
                        activeTooltip = menuTooltip;
                    }, 100);
                }
            });

            link.addEventListener('mouseleave', function () {
                clearTimeout(tooltipTimeout);
                if (activeTooltip) {
                    activeTooltip.classList.remove('show');
                    activeTooltip = null;
                }
            });
        });

        // Sidebar geçiş sırasında tooltip temizle
        adminSidebar.addEventListener('transitionstart', function () {
            if (activeTooltip) {
                activeTooltip.classList.remove('show');
                activeTooltip = null;
            }
            clearTimeout(tooltipTimeout);
        });
    });
</script>
</body>

</html>