<?php

include 'fileA.php';
include 'fileB.php';

use Library\Database\Connection as DbConnection;
use Library\API\Connection as ApiConnection;

$dbConn = new DbConnection();
$dbConn->connect(); 
$apiConn = new ApiConnection();
$apiConn->connect();
?>