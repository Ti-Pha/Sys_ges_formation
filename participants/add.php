<?php
include '../config.php';
include '../functions.php';
include '../session.php';


requireAdmin();

$message = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$participant = null;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM participants WHERE id = ?');
        $stmt->execute([$id]);
        $participant = $stmt->fetch();
        
        if (!$participant) {
            include '../header.php'; 
            echo getErrorMessage('Participant non trouvé');
            include '../footer.php';
            exit;
        }
    } catch (Exception $e) {
        include '../header.php'; 
        echo getErrorMessage('Erreur: ' . $e->getMessage());
        include '../footer.php';
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $date_inscription = $_POST['date_inscription'] ?? date('Y-m-d');
    $statut = $_POST['statut'] ?? 'inscrit';
    
    if (!$nom || !$prenom || !$email) {
        $message = getErrorMessage('Tous les champs obligatoires doivent être remplis');
    } else {
        try {
            // ===== DÉBUT TRANSACTION =====
            if (!startTransaction($pdo)) {
                throw new Exception('Impossible de démarrer la transaction');
            }
            
            if ($id > 0) {
                // UPDATE
                $stmt = $pdo->prepare('UPDATE participants SET nom=?, prenom=?, email=?, telephone=?, date_inscription=?, statut=? WHERE id=?');
                $success = $stmt->execute([$nom, $prenom, $email, $telephone, $date_inscription, $statut, $id]);
                $action = 'mise à jour';
            } else {
                // INSERT
                $stmt = $pdo->prepare('INSERT INTO participants (nom, prenom, email, telephone, date_inscription, statut) VALUES (?, ?, ?, ?, ?, ?)');
                $success = $stmt->execute([$nom, $prenom, $email, $telephone, $date_inscription, $statut]);
                $action = 'création';
            }
            
            if ($success) {
                // ===== COMMIT =====
                if (!commit($pdo)) {
                    throw new Exception('Impossible de valider la transaction');
                }
                
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

<h2 class="mb-4"><?php echo $id > 0 ? 'Modifier' : 'Ajouter'; ?> un Participant</h2>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" required 
                                   value="<?php echo $participant ? htmlspecialchars($participant['nom']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" name="prenom" required 
                                   value="<?php echo $participant ? htmlspecialchars($participant['prenom']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required 
                               value="<?php echo $participant ? htmlspecialchars($participant['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" name="telephone" 
                               value="<?php echo $participant ? htmlspecialchars($participant['telephone']) : ''; ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date d'inscription</label>
                            <input type="date" class="form-control" name="date_inscription" 
                                   value="<?php echo $participant ? $participant['date_inscription'] : date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="statut">
                                <option value="inscrit" <?php echo (!$participant || $participant['statut'] == 'inscrit') ? 'selected' : ''; ?>>Inscrit</option>
                                <option value="en_cours" <?php echo ($participant && $participant['statut'] == 'en_cours') ? 'selected' : ''; ?>>En cours</option>
                                <option value="terminé" <?php echo ($participant && $participant['statut'] == 'terminé') ? 'selected' : ''; ?>>Terminé</option>
                                <option value="abandonné" <?php echo ($participant && $participant['statut'] == 'abandonné') ? 'selected' : ''; ?>>Abandonné</option>
                            </select>
                        </div>
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