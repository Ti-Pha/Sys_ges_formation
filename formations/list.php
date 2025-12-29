<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

requireAdmin();

try {
    $stmt = $pdo->query('SELECT * FROM formations ORDER BY date_debut DESC');
    $formations = $stmt->fetchAll();
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    $formations = [];
}
?>

<h2 class="mb-4">Gestion des Formations</h2>
<a href="add.php" class="btn btn-success mb-3">+ Ajouter une Formation</a>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Titre</th>
                <th>Instructeur</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Durée (h)</th>
                <th>Prix</th>
                <th>Participants</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($formations as $formation): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($formation['titre']); ?></strong></td>
                <td><?php echo htmlspecialchars($formation['instructeur']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($formation['date_debut'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($formation['date_fin'])); ?></td>
                <td><?php echo $formation['duree_heures']; ?></td>
                <td><?php echo number_format($formation['prix_unitaire'], 2, ',', ' '); ?> €</td>
                <td><span class="badge bg-info"><?php echo $formation['nombre_participants']; ?></span></td>
                <td>
                    <span class="badge bg-<?php 
                        echo ($formation['statut'] == 'planifiée') ? 'secondary' : 
                             (($formation['statut'] == 'en_cours') ? 'primary' : 
                             (($formation['statut'] == 'terminée') ? 'success' : 'danger'));
                    ?>">
                        <?php echo ucfirst($formation['statut']); ?>
                    </span>
                </td>
                <td>
                    <a href="view.php?id=<?php echo $formation['id']; ?>" class="btn btn-sm btn-info">👁️</a>
                    <a href="edit.php?id=<?php echo $formation['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                    <a href="delete.php?id=<?php echo $formation['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression?')">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>
