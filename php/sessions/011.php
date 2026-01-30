<?php

setcookie("user_preference", "dark_mode", time() + 3600, "/"); 

if(isset($_COOKIE["user_preference"])){
    echo "User Preference: " . $_COOKIE["user_preference"];
} else {
    echo "No user preference set.";
}
?>