<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

$current_user = getCurrentUser();

// Récupérer les données personnelles du participant lié à cet utilisateur
try {
    // Récupérer le participant lié
    $stmt = $pdo->prepare('SELECT id FROM participants WHERE email = ?');
    $stmt->execute([$current_user['email']]);
    $participant = $stmt->fetch();
    
    if (!$participant) {
        echo getErrorMessage('Erreur: Aucun profil participant trouvé pour cet utilisateur.');
        include '../footer.php';
        exit;
    }
    
    $participant_id = $participant['id'];
    
    // Formations auxquelles l'utilisateur est inscrit
    $stmt = $pdo->prepare('
        SELECT f.id, f.titre, f.instructeur, f.date_debut, f.date_fin, f.statut, 
               i.id as inscription_id, i.statut as inscription_statut,
               e.note_finale, e.resultat, e.certificat_delivre
        FROM inscriptions i
        JOIN formations f ON i.formation_id = f.id
        LEFT JOIN evaluations e ON i.id = e.inscription_id
        WHERE i.participant_id = ?
        ORDER BY f.date_debut DESC
    ');
    $stmt->execute([$participant_id]);
    $mes_formations = $stmt->fetchAll();
    
    // Statistiques personnelles
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM inscriptions WHERE participant_id = ?');
    $stmt->execute([$participant_id]);
    $inscriptions_count = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as total 
        FROM inscriptions i
        JOIN formations f ON i.formation_id = f.id
        WHERE i.participant_id = ? AND f.statut = "en_cours"
    ');
    $stmt->execute([$participant_id]);
    $formations_en_cours = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as total 
        FROM evaluations e
        JOIN inscriptions i ON e.inscription_id = i.id
        WHERE i.participant_id = ? AND e.certificat_delivre = 1
    ');
    $stmt->execute([$participant_id]);
    $certificats_count = $stmt->fetch()['total'];
    
    // Moyenne générale
    $stmt = $pdo->prepare('
        SELECT AVG(e.note_finale) as moyenne
        FROM evaluations e
        JOIN inscriptions i ON e.inscription_id = i.id
        WHERE i.participant_id = ? AND e.note_finale IS NOT NULL
    ');
    $stmt->execute([$participant_id]);
    $moyenne_result = $stmt->fetch();
    $moyenne = $moyenne_result['moyenne'] ? round($moyenne_result['moyenne'], 2) : 0;
    
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    $mes_formations = [];
}

?>

<h1 class="mb-4">Mon Tableau de Bord</h1>

<div class="alert alert-success" role="alert">
    <strong>Bienvenue <?php echo htmlspecialchars($current_user['prenom'] . ' ' . $current_user['nom']); ?> !</strong>
    Consultez vos formations, vos notes et vos certificats.
</div>

<!-- Statistiques personnelles -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="stat-number"></div>
            <div class="stat-number"><?php echo $inscriptions_count; ?></div>
            <div class="stat-label">Formations Inscrites</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="stat-number"></div>
            <div class="stat-number"><?php echo $formations_en_cours; ?></div>
            <div class="stat-label">En Cours</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="stat-number"></div>
            <div class="stat-number"><?php echo $certificats_count; ?></div>
            <div class="stat-label">Certificats Reçus</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="stat-number"></div>
            <div class="stat-number"><?php echo $moyenne; ?>/20</div>
            <div class="stat-label">Moyenne Générale</div>
        </div>
    </div>
</div>

<!-- Mes formations -->
<div class="row">
    <div class="col-md-12">
        <h4 class="mb-3">Mes Formations</h4>
        <?php if (count($mes_formations) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Titre</th>
                        <th>Instructeur</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th>Note</th>
                        <th>Résultat</th>
                        <th>Certificat</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mes_formations as $f): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($f['titre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($f['instructeur']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($f['date_debut'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($f['date_fin'])); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo ($f['statut'] == 'planifiée') ? 'secondary' : 
                                     (($f['statut'] == 'en_cours') ? 'primary' : 'success');
                            ?>">
                                <?php echo ucfirst($f['statut']); ?>
                            </span>
                        </td>
                        <td><?php echo $f['note_finale'] ? number_format($f['note_finale'], 2, ',', ' ') : '-'; ?>/20</td>
                        <td>
                            <?php if ($f['resultat']): ?>
                                <span class="badge bg-<?php echo $f['resultat'] == 'réussi' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($f['resultat']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $f['certificat_delivre'] ? '✓ Oui' : '-'; ?>
                        </td>
                        <td>
                            <a href="/Sys_ges_formation/formations/view.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-primary">👁️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            Vous n'êtes inscrit à aucune formation pour le moment.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
