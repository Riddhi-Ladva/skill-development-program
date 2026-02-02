<?php

/*
 * Count the number of times a user has visited this page using PHP sessions.
 */

session_start();
$_SESSION['visited'] = isset($_SESSION['visited']) ? $_SESSION['visited'] + 1 : 1;

if ($_SESSION['visited'] == 1) {
    echo "Welcome! This is your first visit.";
} else {
    echo "You have visited this page " . $_SESSION['visited'] . " times.";
}
?>