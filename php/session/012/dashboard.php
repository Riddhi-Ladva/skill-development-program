<?php
session_start();

if (isset($_SESSION['username'])) {
    echo "Welcome " . $_SESSION['username'] . "\n";
} else {
    echo "No active session\n";
}
?>
