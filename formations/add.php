<?php
include '../config.php';
include '../functions.php';
include '../session.php';


requireAdmin();

$message = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$formation = null;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM formations WHERE id = ?');
        $stmt->execute([$id]);
        $formation = $stmt->fetch();
        
        if (!$formation) {
            echo getErrorMessage('Formation non trouvée');
            include '../footer.php';
            exit;
        }
    } catch (Exception $e) {
        echo getErrorMessage('Erreur: ' . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $instructeur = $_POST['instructeur'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $duree_heures = (int)($_POST['duree_heures'] ?? 0);
    $prix_unitaire = (float)($_POST['prix_unitaire'] ?? 0);
    $statut = $_POST['statut'] ?? 'planifiée';
    
    if (!$titre || !$instructeur || !$date_debut || !$date_fin || !$duree_heures || !$prix_unitaire) {
        $message = getErrorMessage('Tous les champs obligatoires doivent être remplis');
    } else {
        try {
            // ===== DÉBUT TRANSACTION =====
            if (!startTransaction($pdo)) {
                throw new Exception('Impossible de démarrer la transaction');
            }
            
            if ($id > 0) {
                // UPDATE
                $stmt = $pdo->prepare('UPDATE formations SET titre=?, description=?, instructeur=?, date_debut=?, date_fin=?, duree_heures=?, prix_unitaire=?, statut=? WHERE id=?');
                $success = $stmt->execute([$titre, $description, $instructeur, $date_debut, $date_fin, $duree_heures, $prix_unitaire, $statut, $id]);
                $action = 'mise à jour';
            } else {
                // INSERT
                $stmt = $pdo->prepare('INSERT INTO formations (titre, description, instructeur, date_debut, date_fin, duree_heures, prix_unitaire, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $success = $stmt->execute([$titre, $description, $instructeur, $date_debut, $date_fin, $duree_heures, $prix_unitaire, $statut]);
                $action = 'création';
            }
            
            if ($success) {
                // ===== COMMIT =====
                if (!commit($pdo)) {
                    throw new Exception('Impossible de valider la transaction');
                }
                $message = getSuccessMessage('Formation ' . $action . ' avec succès');
                header('Location: list.php');
                exit;
            } else {
                // ===== ROLLBACK =====
                rollback($pdo);
                throw new Exception('Erreur lors de ' . $action);
            }
        } catch (Exception $e) {
            $message = getErrorMessage('Erreur: ' . $e->getMessage());
        }
    }
}

include '../header.php';
?>

<h2 class="mb-4"><?php echo $id > 0 ? 'Modifier' : 'Ajouter'; ?> une Formation</h2>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Titre *</label>
                        <input type="text" class="form-control" name="titre" required 
                               value="<?php echo $formation ? htmlspecialchars($formation['titre']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo $formation ? htmlspecialchars($formation['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Instructeur *</label>
                        <input type="text" class="form-control" name="instructeur" required 
                               value="<?php echo $formation ? htmlspecialchars($formation['instructeur']) : ''; ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date de début *</label>
                            <input type="date" class="form-control" name="date_debut" required 
                                   value="<?php echo $formation ? $formation['date_debut'] : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date de fin *</label>
                            <input type="date" class="form-control" name="date_fin" required 
                                   value="<?php echo $formation ? $formation['date_fin'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durée (heures) *</label>
                            <input type="number" class="form-control" name="duree_heures" required min="1"
                                   value="<?php echo $formation ? $formation['duree_heures'] : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prix unitaire (€) *</label>
                            <input type="number" class="form-control" name="prix_unitaire" required step="0.01" min="0"
                                   value="<?php echo $formation ? $formation['prix_unitaire'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="statut">
                            <option value="planifiée" <?php echo ($formation && $formation['statut'] == 'planifiée') ? 'selected' : ''; ?>>Planifiée</option>
                            <option value="en_cours" <?php echo ($formation && $formation['statut'] == 'en_cours') ? 'selected' : ''; ?>>En cours</option>
                            <option value="terminée" <?php echo ($formation && $formation['statut'] == 'terminée') ? 'selected' : ''; ?>>Terminée</option>
                            <option value="annulée" <?php echo ($formation && $formation['statut'] == 'annulée') ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="list.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
