<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

// Vérifier si l'utilisateur est admin
requireAdmin();

// Récupérer les statistiques
try {
    // Nombre total de formations
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM formations');
    $formations_count = $stmt->fetch()['total'];
    
    // Nombre total de participants
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM participants');
    $participants_count = $stmt->fetch()['total'];
    
    // Nombre total d'inscriptions
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM inscriptions');
    $inscriptions_count = $stmt->fetch()['total'];
    
    // Nombre total d'évaluations complétées
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM evaluations WHERE resultat != "en_attente"');
    $evaluations_count = $stmt->fetch()['total'];
    
    // Formations en cours
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM formations WHERE statut = "en_cours"');
    $formations_en_cours = $stmt->fetch()['total'];
    
    // Taux de réussite
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM evaluations WHERE resultat = "réussi"');
    $reussis = $stmt->fetch()['total'];
    $taux_reussite = $evaluations_count > 0 ? round(($reussis / $evaluations_count) * 100, 2) : 0;
    
    // Dernières formations créées
    $stmt = $pdo->query('
        SELECT id, titre, date_debut, nombre_participants, statut
        FROM formations
        ORDER BY created_at DESC
        LIMIT 5
    ');
    $dernières_formations = $stmt->fetchAll();
    
    // Évaluations en attente
    $stmt = $pdo->query('
        SELECT e.id, p.prenom, p.nom, f.titre as formation_titre
        FROM evaluations e
        JOIN inscriptions i ON e.inscription_id = i.id
        JOIN participants p ON i.participant_id = p.id
        JOIN formations f ON i.formation_id = f.id
        WHERE e.resultat = "en_attente"
        LIMIT 5
    ');
    $evaluations_en_attente = $stmt->fetchAll();
    
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
}

$current_user = getCurrentUser();
?>

<h1 class="mb-4">Tableau de Bord Admin</h1>

<div class="alert alert-info" role="alert">
    <strong>Bienvenue <?php echo htmlspecialchars($current_user['prenom'] . ' ' . $current_user['nom']); ?> !</strong>
    Vous êtes connecté en tant qu'administrateur.
</div>

<!-- Statistiques principales -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="stat-number"></div>
            <div class="stat-number text-info"><?php echo $formations_count; ?></div>
            <div class="stat-label">Formations</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="stat-number"></div>
            <div class="stat-number text-success"><?php echo $participants_count; ?></div>
            <div class="stat-label">Participants</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="stat-number"></div>
            <div class="stat-number text-warning"><?php echo $inscriptions_count; ?></div>
            <div class="stat-label">Inscriptions</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="stat-number"></div>
            <div class="stat-number text-success"><?php echo $taux_reussite; ?>%</div>
            <div class="stat-label">Taux de Réussite</div>
        </div>
    </div>
</div>

<!-- Lignes supplémentaires de stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="card-title">Formations en Cours</h6>
                <p class="fs-4 fw-bold text-primary"><?php echo $formations_en_cours; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="card-title">Évaluations Complétées</h6>
                <p class="fs-4 fw-bold text-success"><?php echo $evaluations_count; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="card-title">Évaluations en Attente</h6>
                <p class="fs-4 fw-bold text-warning"><?php echo count($evaluations_en_attente); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="row mb-4">
    <div class="col-md-12">
        <h4 class="mb-3">Actions Rapides</h4>
        <div class="btn-group" role="group">
            <a href="/Sys_ges_formation/admin/approve_users.php" class="btn btn-warning">Approbations Utilisateurs</a>
            <a href="/Sys_ges_formation/formations/add.php" class="btn btn-primary">+ Ajouter Formation</a>
            <a href="/Sys_ges_formation/participants/add.php" class="btn btn-success">+ Ajouter Participant</a>
            <a href="/Sys_ges_formation/evaluations/add.php" class="btn btn-info">+ Ajouter Évaluation</a>
            <a href="/Sys_ges_formation/formations/list.php" class="btn btn-secondary">Voir Toutes les Formations</a>
        </div>
    </div>
</div>

<!-- Dernières formations -->
<div class="row">
    <div class="col-md-6">
        <h4 class="mb-3">Dernières Formations</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Titre</th>
                        <th>Début</th>
                        <th>Participants</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernières_formations as $f): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(substr($f['titre'], 0, 20)); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($f['date_debut'])); ?></td>
                        <td><span class="badge bg-info"><?php echo $f['nombre_participants']; ?></span></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo ($f['statut'] == 'planifiée') ? 'secondary' : 
                                     (($f['statut'] == 'en_cours') ? 'primary' : 
                                     (($f['statut'] == 'terminée') ? 'success' : 'danger'));
                            ?>">
                                <?php echo ucfirst($f['statut']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Évaluations en attente -->
    <div class="col-md-6">
        <h4 class="mb-3">Évaluations en Attente</h4>
        <?php if (count($evaluations_en_attente) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Participant</th>
                        <th>Formation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluations_en_attente as $e): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($e['prenom'] . ' ' . $e['nom']); ?></td>
                        <td><?php echo htmlspecialchars(substr($e['formation_titre'], 0, 20)); ?></td>
                        <td>
                            <a href="/Sys_ges_formation/evaluations/edit.php?id=<?php echo $e['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-success">Aucune évaluation en attente</div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
