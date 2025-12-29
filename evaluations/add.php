<?php
include '../config.php';
include '../functions.php';
include '../session.php';


requireAdmin();

$message = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$evaluation = null;
$inscriptions = [];

// Récupérer les inscriptions disponibles
try {
    $stmt = $pdo->query('
        SELECT 
            i.id,
            p.prenom,
            p.nom,
            f.titre as formation_titre
        FROM inscriptions i
        JOIN participants p ON i.participant_id = p.id
        JOIN formations f ON i.formation_id = f.id
        ORDER BY p.nom, p.prenom
    ');
    $inscriptions = $stmt->fetchAll();
} catch (Exception $e) {
    echo getErrorMessage('Erreur lors du chargement des inscriptions: ' . $e->getMessage());
}

if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM evaluations WHERE id = ?');
        $stmt->execute([$id]);
        $evaluation = $stmt->fetch();
        
        if (!$evaluation) {
            echo getErrorMessage('Évaluation non trouvée');
            include '../footer.php';
            exit;
        }
    } catch (Exception $e) {
        echo getErrorMessage('Erreur: ' . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inscription_id = (int)($_POST['inscription_id'] ?? 0);
    $note_devoir = isset($_POST['note_devoir']) && $_POST['note_devoir'] !== '' ? (float)$_POST['note_devoir'] : null;
    $note_test = isset($_POST['note_test']) && $_POST['note_test'] !== '' ? (float)$_POST['note_test'] : null;
    $note_participation = isset($_POST['note_participation']) && $_POST['note_participation'] !== '' ? (float)$_POST['note_participation'] : null;
    $certificat_delivre = isset($_POST['certificat_delivre']) ? 1 : 0;
    
    if (!$inscription_id) {
        $message = getErrorMessage('Vous devez sélectionner une inscription');
    } else {
        try {
            // Calculer la note finale et le résultat
            $note_finale = null;
            $resultat = 'en_attente';
            
            if ($note_devoir !== null || $note_test !== null || $note_participation !== null) {
                $note_finale = (($note_devoir ?? 0) * 0.3) + (($note_test ?? 0) * 0.5) + (($note_participation ?? 0) * 0.2);
                $resultat = ($note_finale >= 12) ? 'réussi' : 'échoué';
            }
            
            // Logique du certificat: ne peut être délivré que si réussi
            if ($resultat !== 'réussi') {
                $certificat_delivre = 0;
            }
            
            // ===== DÉBUT TRANSACTION =====
            if (!startTransaction($pdo)) {
                throw new Exception('Impossible de démarrer la transaction');
            }
            
            if ($id > 0) {
                // UPDATE
                $stmt = $pdo->prepare('
                    UPDATE evaluations 
                    SET inscription_id=?, note_devoir=?, note_test=?, note_participation=?, note_finale=?, resultat=?, certificat_delivre=?
                    WHERE id=?
                ');
                $success = $stmt->execute([$inscription_id, $note_devoir, $note_test, $note_participation, $note_finale, $resultat, $certificat_delivre, $id]);
                $action = 'mise à jour';
            } else {
                // INSERT
                $stmt = $pdo->prepare('
                    INSERT INTO evaluations (inscription_id, note_devoir, note_test, note_participation, note_finale, resultat, certificat_delivre)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $success = $stmt->execute([$inscription_id, $note_devoir, $note_test, $note_participation, $note_finale, $resultat, $certificat_delivre]);
                $action = 'création';
            }
            
            if ($success) {
                // ===== COMMIT =====
                if (!commit($pdo)) {
                    throw new Exception('Impossible de valider la transaction');
                }
                $certificat_msg = ($resultat === 'réussi' && $certificat_delivre) ? ' Certificat délivré! 🏆' : '';
                $message = getSuccessMessage('Évaluation ' . $action . ' avec succès. (Résultat: ' . ucfirst($resultat) . ')' . $certificat_msg);
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

<h2 class="mb-4"><?php echo $id > 0 ? 'Modifier' : 'Ajouter'; ?> une Évaluation</h2>

<?php echo $message; ?>

<div class="alert alert-info" role="alert">
    <strong>Formule de calcul:</strong> Note Finale = (Devoir × 30%) + (Test × 50%) + (Participation × 20%)
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Participant / Formation *</label>
                        <select class="form-select" name="inscription_id" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($inscriptions as $inscription): ?>
                            <option value="<?php echo $inscription['id']; ?>" 
                                    <?php echo ($evaluation && $evaluation['inscription_id'] == $inscription['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom'] . ' - ' . $inscription['formation_titre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <fieldset class="mb-4">
                        <legend>Notes (sur 20)</legend>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Devoir (30%)</label>
                                <input type="number" class="form-control" name="note_devoir" step="0.01" min="0" max="20"
                                       value="<?php echo $evaluation && $evaluation['note_devoir'] ? $evaluation['note_devoir'] : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Test (50%)</label>
                                <input type="number" class="form-control" name="note_test" step="0.01" min="0" max="20"
                                       value="<?php echo $evaluation && $evaluation['note_test'] ? $evaluation['note_test'] : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Participation (20%)</label>
                                <input type="number" class="form-control" name="note_participation" step="0.01" min="0" max="20"
                                       value="<?php echo $evaluation && $evaluation['note_participation'] ? $evaluation['note_participation'] : ''; ?>">
                            </div>
                        </div>
                    </fieldset>
                    
                    <?php if ($evaluation): ?>
                    <div class="alert alert-success" role="alert">
                        <strong>Note Finale Calculée:</strong> 
                        <span class="fs-5"><?php echo number_format($evaluation['note_finale'], 2, ',', ' '); ?>/20</span>
                        <br>
                        <strong>Résultat:</strong> 
                        <span class="badge bg-<?php echo $evaluation['resultat'] == 'réussi' ? 'success' : ($evaluation['resultat'] == 'échoué' ? 'danger' : 'secondary'); ?>">
                            <?php echo ucfirst($evaluation['resultat']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <fieldset class="mb-3">
                        <legend class="fs-6">Certificat de Réussite</legend>
                        <div class="alert alert-warning mb-3">
                            <p class="mb-2">
                                <strong>Le certificat ne peut être délivré que si :</strong>
                            </p>
                            <ul class="mb-0">
                                <li>Le participant a <strong>RÉUSSI</strong> (note finale ≥ 12)</li>
                                <li>Impossible si note finale < 12 (échoué)</li>
                                <li>Impossible si les notes ne sont pas encore évaluées</li>
                            </ul>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="certificat_delivre" name="certificat_delivre"
                                   <?php echo ($evaluation && $evaluation['certificat_delivre']) ? 'checked' : ''; ?>
                                   <?php echo ($evaluation && $evaluation['resultat'] !== 'réussi') ? 'disabled' : ''; ?>>
                            <label class="form-check-label" for="certificat_delivre">
                                Certificat délivré au participant
                            </label>
                        </div>
                        
                        <?php if ($evaluation && $evaluation['resultat'] !== 'réussi'): ?>
                        <small class="text-danger d-block mt-2">
                            <strong>⚠️ Le certificat ne peut pas être délivré car le participant n'a pas réussi (résultat: <?php echo ucfirst($evaluation['resultat']); ?>)</strong>
                        </small>
                        <?php endif; ?>
                    </fieldset>
                    
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
