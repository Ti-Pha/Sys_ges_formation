<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

requireAdmin();

$message = '';
$formation_id = isset($_GET['formation_id']) ? (int)$_GET['formation_id'] : 0;
$formation = null;
$participants = [];

if ($formation_id <= 0) {
    echo getErrorMessage('Formation non trouvée');
    include '../footer.php';
    exit;
}

// Récupérer la formation
try {
    $stmt = $pdo->prepare('SELECT * FROM formations WHERE id = ?');
    $stmt->execute([$formation_id]);
    $formation = $stmt->fetch();
    
    if (!$formation) {
        echo getErrorMessage('Formation non trouvée');
        include '../footer.php';
        exit;
    }
    
    // Récupérer les participants non encore inscrits à cette formation
    $stmt = $pdo->prepare('
        SELECT * FROM participants 
        WHERE id NOT IN (SELECT participant_id FROM inscriptions WHERE formation_id = ?)
        ORDER BY nom, prenom
    ');
    $stmt->execute([$formation_id]);
    $participants = $stmt->fetchAll();
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    include '../footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_existing') {
        // Ajouter un participant existant
        $participant_id = (int)($_POST['participant_id'] ?? 0);
        
        if ($participant_id <= 0) {
            $message = getErrorMessage('Veuillez sélectionner un participant');
        } else {
            try {
                // Vérifier que le participant existe
                $stmt = $pdo->prepare('SELECT id FROM participants WHERE id = ?');
                $stmt->execute([$participant_id]);
                if (!$stmt->fetch()) {
                    throw new Exception('Participant non trouvé');
                }
                
                // ===== DÉBUT TRANSACTION =====
                if (!startTransaction($pdo)) {
                    throw new Exception('Impossible de démarrer la transaction');
                }
                
                // Ajouter l'inscription
                $stmt = $pdo->prepare('INSERT INTO inscriptions (participant_id, formation_id, statut) VALUES (?, ?, ?)');
                $success = $stmt->execute([$participant_id, $formation_id, 'inscrit']);
                
                if ($success) {
                    // Mettre à jour le nombre de participants
                    $stmt = $pdo->prepare('UPDATE formations SET nombre_participants = nombre_participants + 1 WHERE id = ?');
                    $stmt->execute([$formation_id]);
                    
                    // ===== COMMIT =====
                    if (!commit($pdo)) {
                        throw new Exception('Impossible de valider la transaction');
                    }
                    $message = getSuccessMessage('Participant ajouté avec succès à la formation');
                    
                    // Recharger la liste des participants disponibles
                    $stmt = $pdo->prepare('
                        SELECT * FROM participants 
                        WHERE id NOT IN (SELECT participant_id FROM inscriptions WHERE formation_id = ?)
                        ORDER BY nom, prenom
                    ');
                    $stmt->execute([$formation_id]);
                    $participants = $stmt->fetchAll();
                } else {
                    // ===== ROLLBACK =====
                    rollback($pdo);
                    throw new Exception('Erreur lors de l\'ajout du participant');
                }
            } catch (Exception $e) {
                $message = getErrorMessage('Erreur: ' . $e->getMessage());
            }
        }
    } 
    elseif ($action === 'add_new') {
        // Créer un nouveau participant et l'ajouter à la formation
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        
        if (!$nom || !$prenom || !$email) {
            $message = getErrorMessage('Tous les champs obligatoires (Nom, Prénom, Email) doivent être remplis');
        } else {
            try {
                // ===== DÉBUT TRANSACTION =====
                if (!startTransaction($pdo)) {
                    throw new Exception('Impossible de démarrer la transaction');
                }
                
                // Créer le participant
                $stmt = $pdo->prepare('INSERT INTO participants (nom, prenom, email, telephone, date_inscription, statut) VALUES (?, ?, ?, ?, ?, ?)');
                $success = $stmt->execute([$nom, $prenom, $email, $telephone, date('Y-m-d'), 'inscrit']);
                
                if ($success) {
                    $participant_id = $pdo->lastInsertId();
                    
                    // Ajouter l'inscription à la formation
                    $stmt = $pdo->prepare('INSERT INTO inscriptions (participant_id, formation_id, statut) VALUES (?, ?, ?)');
                    $success = $stmt->execute([$participant_id, $formation_id, 'inscrit']);
                    
                    if ($success) {
                        // Mettre à jour le nombre de participants
                        $stmt = $pdo->prepare('UPDATE formations SET nombre_participants = nombre_participants + 1 WHERE id = ?');
                        $stmt->execute([$formation_id]);
                        
                        // ===== COMMIT =====
                        if (!commit($pdo)) {
                            throw new Exception('Impossible de valider la transaction');
                        }
                        $message = getSuccessMessage('Nouveau participant créé et ajouté à la formation avec succès');
                        
                        // Recharger la liste des participants disponibles
                        $stmt = $pdo->prepare('
                            SELECT * FROM participants 
                            WHERE id NOT IN (SELECT participant_id FROM inscriptions WHERE formation_id = ?)
                            ORDER BY nom, prenom
                        ');
                        $stmt->execute([$formation_id]);
                        $participants = $stmt->fetchAll();
                    } else {
                        // ===== ROLLBACK =====
                        rollback($pdo);
                        throw new Exception('Erreur lors de l\'ajout du participant à la formation');
                    }
                } else {
                    // ===== ROLLBACK =====
                    rollback($pdo);
                    throw new Exception('Erreur lors de la création du participant');
                }
            } catch (Exception $e) {
                $message = getErrorMessage('Erreur: ' . $e->getMessage());
            }
        }
    }
}
?>

<div class="mb-3">
    <a href="view.php?id=<?php echo $formation_id; ?>" class="btn btn-secondary">← Retour à la formation</a>
</div>

<?php if ($formation): ?>

<h2 class="mb-4">Ajouter un participant à : <strong><?php echo htmlspecialchars($formation['titre']); ?></strong></h2>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-8">
        <!-- TAB 1: Ajouter un participant existant -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="existing-tab" data-bs-toggle="tab" data-bs-target="#existing" type="button" role="tab">
                    Ajouter un participant existant
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button" role="tab">
                    Créer un nouveau participant
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Onglet: Participant existant -->
            <div class="tab-pane fade show active" id="existing" role="tabpanel">
                <?php if (count($participants) > 0): ?>
                    <form method="POST" class="card p-4 bg-light">
                        <input type="hidden" name="action" value="add_existing">
                        
                        <div class="mb-3">
                            <label for="participant_id" class="form-label">Sélectionner un participant <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="participant_id" name="participant_id" required>
                                <option value="">-- Choisir un participant --</option>
                                <?php foreach ($participants as $p): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?> (<?php echo htmlspecialchars($p['email']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg">✓ Ajouter à la formation</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>Tous les participants sont déjà inscrits à cette formation.</p>
                        <p>Créez un nouveau participant avec l'onglet ci-dessus.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Onglet: Nouveau participant -->
            <div class="tab-pane fade" id="new" role="tabpanel">
                <form method="POST" class="card p-4 bg-light">
                    <input type="hidden" name="action" value="add_new">
                    
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="prenom" name="prenom" required placeholder="Jean">
                    </div>
                    
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="nom" name="nom" required placeholder="Dupont">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required placeholder="jean.dupont@email.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="tel" class="form-control form-control-lg" id="telephone" name="telephone" placeholder="06 12 34 56 78">
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg">✓ Créer et ajouter à la formation</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<p class="text-danger">Formation non trouvée.</p>
<?php endif; ?>

<?php include '../footer.php'; ?>
