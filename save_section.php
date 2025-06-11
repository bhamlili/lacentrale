<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['section'])) {
    $_SESSION['active_section'] = $_POST['section'];
    echo 'success';
}
