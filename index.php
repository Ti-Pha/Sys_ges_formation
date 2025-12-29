<?php
include 'session.php';

// Si connecté, rediriger vers le dashboard approprié
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: /Sys_ges_formation/admin/index.php');
    } else {
        header('Location: /Sys_ges_formation/user/index.php');
    }
    exit;
}

// Si NON connecté, rediriger vers la page de login
header('Location: /Sys_ges_formation/login.php');
exit;
?>
