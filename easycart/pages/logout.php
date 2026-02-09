<?php
require_once '../includes/bootstrap/session.php';

// Prevent caching of this logout page
require_once '../includes/auth/cache_control.php';

// Clear session
// Clear session but do NOT clear DB cart (it persists for the user)
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

?>
<script>
    // Clear client-side storage
    localStorage.removeItem('wishlist');

    // Redirect to home and replace history to prevent back button navigation to this page
    window.location.replace('<?php echo url('index'); ?>');
</script>
<?php
exit;
?>