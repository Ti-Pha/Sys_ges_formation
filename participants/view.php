<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo getErrorMessage('Participant non trouvé');
    include '../footer.php';
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM participants WHERE id = ?');
    $stmt->execute([$id]);
    $participant = $stmt->fetch();
    
    if (!$participant) {
        echo getErrorMessage('Participant non trouvé');
        include '../footer.php';
        exit;
    }
    
    $stmt = $pdo->prepare('
        SELECT f.id, f.titre, f.date_debut, f.date_fin, i.date_inscription, i.statut, e.note_finale, e.resultat
        FROM inscriptions i
        JOIN formations f ON i.formation_id = f.id
        LEFT JOIN evaluations e ON i.id = e.inscription_id
        WHERE i.participant_id = ?
        ORDER BY f.date_debut DESC
    ');
    $stmt->execute([$id]);
    $inscriptions = $stmt->fetchAll();
    
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    $participant = null;
    $inscriptions = [];
}
?>

<div class="mb-3">
    <a href="list.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if ($participant): ?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="mb-0"><?php echo htmlspecialchars($participant['prenom'] . ' ' . $participant['nom']); ?></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($participant['email']); ?>"><?php echo htmlspecialchars($participant['email']); ?></a></p>
                <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($participant['telephone'] ?: 'N/A'); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Date d'inscription:</strong> <?php echo date('d/m/Y', strtotime($participant['date_inscription'])); ?></p>
                <p><strong>Statut:</strong> <span class="badge bg-primary"><?php echo ucfirst($participant['statut']); ?></span></p>
            </div>
        </div>
    </div>
</div>

<h4>Formations suivies (<?php echo count($inscriptions); ?>)</h4>

<?php if (count($inscriptions) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Formation</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Statut inscription</th>
                <th>Note finale</th>
                <th>Résultat</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inscriptions as $inscription): ?>
            <tr>
                <td><?php echo htmlspecialchars($inscription['titre']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($inscription['date_debut'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($inscription['date_fin'])); ?></td>
                <td><span class="badge bg-info"><?php echo ucfirst($inscription['statut']); ?></span></td>
                <td><?php echo $inscription['note_finale'] ? number_format($inscription['note_finale'], 2, ',', ' ') : '-'; ?></td>
                <td>
                    <?php if ($inscription['resultat']): ?>
                        <span class="badge bg-<?php echo $inscription['resultat'] == 'réussi' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($inscription['resultat']); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Non évalué</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p class="text-muted">Ce participant n'est inscrit à aucune formation.</p>
<?php endif; ?>

<?php else: ?>
<p class="text-danger">Participant non trouvé.</p>
<?php endif; ?>

<?php include '../footer.php'; ?>
