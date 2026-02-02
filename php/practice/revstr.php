<?php

$str="Hello, World!";

function revstr($s) {
    $reversed = "";
    for ($i = strlen($s) - 1; $i >= 0; $i--) {
        $reversed .= $s[$i];
    }
    return $reversed;
}
echo revstr($str);
?>
