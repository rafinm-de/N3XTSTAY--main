<div class="admin-sidebar">
    <div class="admin-sidebar-header">
        <h3>🏨 Admin Panel</h3>
        <p style="font-size: 0.875rem; color: rgba(255,255,255,0.7); margin: 0.5rem 0 0;">
            <?php echo Session::getUserName(); ?>
        </p>
    </div>

    <nav>
        <ul class="admin-menu">
            <li class="admin-menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="admin-menu-link">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/manage-hotels.php" class="admin-menu-link">
                    <span>🏨</span>
                    <span>Hotels</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/manage-rooms.php" class="admin-menu-link">
                    <span>🛏️</span>
                    <span>Rooms</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/manage-bookings.php" class="admin-menu-link">
                    <span>📅</span>
                    <span>Bookings</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/manage-users.php" class="admin-menu-link">
                    <span>👥</span>
                    <span>Users</span>
                </a>
            </li>
            <li class="admin-menu-item"
                style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                <a href="<?php echo SITE_URL; ?>/index.php" class="admin-menu-link">
                    <span>🌐</span>
                    <span>View Website</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="admin-menu-link">
                    <span>🚪</span>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<script>
    // Highlight active menu item
    document.addEventListener('DOMContentLoaded', function () {
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.admin-menu-link');

        menuLinks.forEach(link => {
            if (link.getAttribute('href').includes(currentPath.split('/').pop())) {
                link.classList.add('active');
            }
        });
    });
</script>