<?php
include '../config.php';
include '../functions.php';
include '../session.php';

requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list.php');
    exit;
}

header('Location: add.php?id=' . $id);
exit;

?>