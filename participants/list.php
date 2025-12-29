<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

requireAdmin();

try {
    $stmt = $pdo->query('SELECT * FROM participants ORDER BY nom ASC');
    $participants = $stmt->fetchAll();
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    $participants = [];
}
?>

<h2 class="mb-4">Gestion des Participants</h2>
<a href="add.php" class="btn btn-success mb-3">+ Ajouter un Participant</a>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Date inscription</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participants as $participant): ?>
            <tr>
                <td><?php echo htmlspecialchars($participant['nom']); ?></td>
                <td><?php echo htmlspecialchars($participant['prenom']); ?></td>
                <td><?php echo htmlspecialchars($participant['email']); ?></td>
                <td><?php echo htmlspecialchars($participant['telephone'] ?: 'N/A'); ?></td>
                <td><?php echo date('d/m/Y', strtotime($participant['date_inscription'])); ?></td>
                <td>
                    <span class="badge bg-<?php 
                        echo ($participant['statut'] == 'inscrit') ? 'secondary' : 
                             (($participant['statut'] == 'en_cours') ? 'primary' : 
                             (($participant['statut'] == 'terminé') ? 'success' : 'danger'));
                    ?>">
                        <?php echo ucfirst($participant['statut']); ?>
                    </span>
                </td>
                <td>
                    <a href="view.php?id=<?php echo $participant['id']; ?>" class="btn btn-sm btn-info">👁️</a>
                    <a href="edit.php?id=<?php echo $participant['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                    <a href="delete.php?id=<?php echo $participant['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression?')">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>
