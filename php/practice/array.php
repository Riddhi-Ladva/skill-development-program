<?php

$users=[
    ["name"=>"Alice","age"=>30],
    ["name"=>"Bob","age"=>25],
    ["name"=>"Charlie","age"=>35],
];
echo "User List:\n";
echo "<table border='1'>\n";
echo "<tr><th>Name</th><th>Age</th></tr>\n";

foreach($users as $user){
    echo "<tr><td>".$user['name']."</td><td>".$user['age']."</td></tr>\n";
}

echo "</table>\n";
?>