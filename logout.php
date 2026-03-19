<?php
session_start();
session_destroy();
header('Location: /DNS_Pharmacy/views/Login.php');
exit;
?>