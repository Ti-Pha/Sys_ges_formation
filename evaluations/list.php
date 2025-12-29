<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

requireAdmin();

try {
    $stmt = $pdo->query('
        SELECT 
            e.id,
            e.note_devoir,
            e.note_test,
            e.note_participation,
            e.note_finale,
            e.resultat,
            e.certificat_delivre,
            p.prenom,
            p.nom,
            f.titre as formation_titre
        FROM evaluations e
        JOIN inscriptions i ON e.inscription_id = i.id
        JOIN participants p ON i.participant_id = p.id
        JOIN formations f ON i.formation_id = f.id
        ORDER BY e.date_evaluation DESC
    ');
    $evaluations = $stmt->fetchAll();
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    $evaluations = [];
}
?>

<h2 class="mb-4">Gestion des Évaluations</h2>
<a href="add.php" class="btn btn-success mb-3">+ Ajouter une Évaluation</a>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Participant</th>
                <th>Formation</th>
                <th>Devoir</th>
                <th>Test</th>
                <th>Participation</th>
                <th>Note Finale</th>
                <th>Résultat</th>
                <th>Certificat</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($evaluations as $evaluation): ?>
            <tr>
                <td><?php echo htmlspecialchars($evaluation['prenom'] . ' ' . $evaluation['nom']); ?></td>
                <td><?php echo htmlspecialchars($evaluation['formation_titre']); ?></td>
                <td><?php echo $evaluation['note_devoir'] ? number_format($evaluation['note_devoir'], 2, ',', ' ') : '-'; ?></td>
                <td><?php echo $evaluation['note_test'] ? number_format($evaluation['note_test'], 2, ',', ' ') : '-'; ?></td>
                <td><?php echo $evaluation['note_participation'] ? number_format($evaluation['note_participation'], 2, ',', ' ') : '-'; ?></td>
                <td>
                    <strong class="<?php echo $evaluation['note_finale'] >= 12 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo number_format($evaluation['note_finale'], 2, ',', ' '); ?>
                    </strong>
                </td>
                <td>
                    <span class="badge bg-<?php echo $evaluation['resultat'] == 'réussi' ? 'success' : ($evaluation['resultat'] == 'échoué' ? 'danger' : 'secondary'); ?>">
                        <?php echo ucfirst($evaluation['resultat']); ?>
                    </span>
                </td>
                <td>
                    <?php if ($evaluation['certificat_delivre']): ?>
                        <span class="badge bg-success"> Oui</span>
                    <?php else: ?>
                        <span class="badge bg-warning"> Non</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="view.php?id=<?php echo $evaluation['id']; ?>" class="btn btn-sm btn-info">👁️</a>
                    <a href="edit.php?id=<?php echo $evaluation['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                    <a href="delete.php?id=<?php echo $evaluation['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression?')">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<br>
<br>
<br>
<?php include '../footer.php'; ?>
