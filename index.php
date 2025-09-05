<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: pages/dashboard.php");
} else {
    header("Location: pages/login.php");
}
exit();
?>
