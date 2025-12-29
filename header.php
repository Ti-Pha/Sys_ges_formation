<?php
// Session et authentification - DOIT ÊTRE EN PREMIER
if (!isset($_SESSION)) {
    @include 'session.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Gestion de Formation Professionnelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Sys_ges_formation/assets/css/muted.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding-top: 20px;
        }
        .navbar {
            background-color: var(--brand);
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background-color: var(--brand);
            border: none;
            color: var(--brand-contrast);
        }
        .btn-primary:hover {
            background-color: #adb5bd;
            color: var(--brand-contrast);
        }
        .dashboard-card {
            text-align: center;
            padding: 30px;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--brand-contrast);
        }
        .stat-label {
            color: #666;
            margin-top: 10px;
        }
        footer {
            background-color: #f1f3f5;
            color: #495057;
            padding: 20px 0;
            margin-top: 40px;
            text-align: center;
        }
        .user-info {
            color: var(--brand-contrast);
            font-size: 0.9rem;
        }
        .badge-role {
            font-size: 0.8rem;
            margin-left: 5px;
            background-color: rgba(255,255,255,0.12);
            color: var(--brand-contrast);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/Sys_ges_formation/<?php echo isLoggedIn() ? (isAdmin() ? 'admin/' : 'user/') : ''; ?>index.php">Gestion Formation</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="/Sys_ges_formation/admin/index.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Sys_ges_formation/formations/list.php">Formations</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Sys_ges_formation/participants/list.php">Participants</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Sys_ges_formation/evaluations/list.php">Évaluations</a></li>
                        <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/Sys_ges_formation/user/index.php">Mon Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/Sys_ges_formation/formations/list.php">Formations</a></li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle user-info" href="#" role="button" data-bs-toggle="dropdown">
                                <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?>
                                <span class="badge badge-role bg-<?php echo isAdmin() ? 'danger' : 'info'; ?>">
                                    <?php echo isAdmin() ? 'ADMIN' : 'USER'; ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" >Mon Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/Sys_ges_formation/logout.php">Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn btn-light text-dark ms-2" href="/Sys_ges_formation/login.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
