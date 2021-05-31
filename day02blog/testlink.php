<?php require './db.php'; ?>
<?php

$email = mysqli_real_escape_string($link, $email);
$password = mysqli_real_escape_string($link, $password);
?>