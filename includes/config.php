<?php
/**
 * Sing App PHP Configuration & Helpers
 */

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Estadísticas 1.7');
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.7.0 (PHP Dark Ready)');
}

/**
 * Determine initial theme from Cookie (Server-side rendering)
 * Defaults to 'dark' to prevent flashes if preferred, or 'light'
 */
function get_current_theme() {
    if (isset($_COOKIE['sing_theme']) && in_array($_COOKIE['sing_theme'], ['light', 'dark'])) {
        return $_COOKIE['sing_theme'];
    }
    return 'dark'; // Default theme
}

/**
 * Helper to determine active sidebar link
 */
function is_active_page($page_identifier, $current_page = null) {
    if ($current_page === null) {
        $current_page = basename($_SERVER['PHP_SELF'], '.php');
    }
    return ($current_page === $page_identifier) ? 'active' : '';
}

/**
 * Helper to determine if a parent submenu should be open
 * Reads from cookie so the state is 100% identical across all views
 */
function is_submenu_open($submenu_id, $pages = [], $current_page = null) {
    if (isset($_COOKIE['sing_open_submenus'])) {
        $openMenus = json_decode($_COOKIE['sing_open_submenus'], true);
        if (is_array($openMenus)) {
            return in_array($submenu_id, $openMenus) ? 'open' : '';
        }
    }
    // By default, keep dashboard open so vertical position of all items remains static
    if ($submenu_id === 'dashboard') {
        return 'open';
    }
    if ($current_page === null) {
        $current_page = basename($_SERVER['PHP_SELF'], '.php');
    }
    $pagesArray = is_array($pages) ? $pages : [$pages];
    return in_array($current_page, $pagesArray) ? 'open' : '';
}

function is_submenu_active($pages = [], $current_page = null) {
    return is_submenu_open('dashboard', $pages, $current_page);
}

/**
 * Helper for Breadcrumbs rendering
 */
function render_breadcrumb($items = []) {
    echo '<ul class="page-breadcrumb">';
    echo '<li><a href="index.php"><i class="bi bi-house-door"></i> Inicio</a></li>';
    foreach ($items as $name => $url) {
        echo '<li class="separator"><i class="bi bi-chevron-right"></i></li>';
        if ($url) {
            echo '<li><a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($name) . '</a></li>';
        } else {
            echo '<li class="active">' . htmlspecialchars($name) . '</li>';
        }
    }
    echo '</ul>';
}

/**
 * Dummy user information for the template
 */
$currentUser = [
    'name' => 'Alexandre Rivera',
    'email' => 'alex.rivera@singapp.io',
    'role' => 'Administrador Senior',
    'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'
];
