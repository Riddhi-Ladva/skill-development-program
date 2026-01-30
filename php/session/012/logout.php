<?php
session_start();

// Remove all session variables
session_unset();

// Destroy session
session_destroy();

echo "You have been logged out\n";
?>
