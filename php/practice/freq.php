
<?php

$banana = "banana";
function freq($s) {
    $frequency = [];
    for ($i = 0; $i < strlen($s); $i++) {
        $char = $s[$i];
        if (isset($frequency[$char])) {
            $frequency[$char]++;
        } else {
            $frequency[$char] = 1;
        }
    }
    return $frequency;
}
$result = freq($banana);
print_r($result);
?>