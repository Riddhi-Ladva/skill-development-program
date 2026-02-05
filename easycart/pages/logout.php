<?php
require_once '../includes/bootstrap/session.php';

// Clear session
// Clear session but do NOT clear DB cart (it persists for the user)
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

session_unset();
session_destroy();

?>
<script>
    localStorage.removeItem('wishlist');
    window.location.href = '../index.php';
</script>
<?php
exit;
?>