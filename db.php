<?php
$servername = "localhost";
$username = "root";
$password = "Ee012345@";
$dbname = "shopyfy";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if(!$conn){
    die("Connection Fialed". mysqli_connect_error());
} else{
    "успех";
}
?>