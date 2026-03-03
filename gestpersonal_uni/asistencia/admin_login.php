<?php
session_start();
$_SESSION['is_admin'] = true;
header("Location: ../index.php");
exit;