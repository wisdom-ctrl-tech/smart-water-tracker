<?php
// ============================================================
// topbar.php
// The blue header used by the dashboard, the scan page and the
// containers page. Set $user_name and $active_page before
// including this file.
// ============================================================
if (!isset($user_name)) {
    $user_name = '';
}
if (!isset($active_page)) {
    $active_page = '';
}
?>
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand">💧 Smart Water Intake Tracker</div>

        <nav class="topbar-nav">
            <a class="nav-link<?php echo $active_page === 'dashboard' ? ' active' : ''; ?>"
               href="index.php">Dashboard</a>
            <a class="nav-link<?php echo $active_page === 'scan' ? ' active' : ''; ?>"
               href="scan.php">Scan</a>
            <a class="nav-link<?php echo $active_page === 'containers' ? ' active' : ''; ?>"
               href="containers.php">Containers</a>
        </nav>

        <div class="topbar-user">
            <span class="hello">Hello, <strong><?php echo e($user_name); ?></strong></span>
            <a class="btn btn-ghost" href="logout.php">Log out</a>
        </div>
    </div>
</header>
