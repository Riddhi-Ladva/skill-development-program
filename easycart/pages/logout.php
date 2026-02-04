<?php
require_once '../includes/bootstrap/session.php';

// Clear session
// Clear session but do NOT clear DB cart (it persists for the user)

session_unset();
session_destroy();

// Redirect to home
header('Location: ../index.php');
exit;
?>