<?php
include '../config.php';
include '../session.php';

// Vérifier que c'est un admin
requireAdmin();

$message = '';
$message_type = '';
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? null;

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? null;
    
    try {
        if ($action === 'approve') {
            $participant_id = $_POST['participant_id'] ?? null;
            
            if (!$user_id || !$participant_id) {
                throw new Exception('Veuillez sélectionner un utilisateur et un participant');
            }
            
            // Approuver l'utilisateur et le lier au participant
            $stmt = $pdo->prepare('UPDATE users SET approved = TRUE, participant_id = ? WHERE id = ?');
            $stmt->execute([$participant_id, $user_id]);
            
            $message = '✓ Utilisateur approuvé et lié au participant';
            $message_type = 'success';
            
        } elseif ($action === 'reject') {
            $rejection_reason = $_POST['rejection_reason'] ?? '';
            
            if (!$user_id) {
                throw new Exception('Utilisateur introuvable');
            }
            
            // Rejeter l'utilisateur
            $stmt = $pdo->prepare('UPDATE users SET approved = FALSE, rejection_reason = ? WHERE id = ?');
            $stmt->execute([$rejection_reason, $user_id]);
            
            $message = '✓ Utilisateur rejeté';
            $message_type = 'info';
        }
    } catch (Exception $e) {
        $message = 'Erreur: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Récupérer les utilisateurs en attente d'approbation
try {
    $stmt = $pdo->prepare('
    SELECT u.id, u.username, u.email, u.nom, u.prenom, u.participant_id, 
           p.nom as participant_nom, p.prenom as participant_prenom
    FROM users u 
    LEFT JOIN participants p ON u.participant_id = p.id
    WHERE u.approved = TRUE AND u.actif = TRUE 
    ORDER BY u.nom, u.prenom
');
    $stmt->execute();
    $pending_users = $stmt->fetchAll();
} catch (Exception $e) {
    $pending_users = [];
    $message = 'Erreur: ' . $e->getMessage();
    $message_type = 'danger';
}

// Récupérer tous les participants pour le formulaire de sélection
try {
    $stmt = $pdo->prepare('SELECT id, nom, prenom, email FROM participants ORDER BY nom, prenom');
    $stmt->execute();
    $participants = $stmt->fetchAll();
} catch (Exception $e) {
    $participants = [];
}

// Récupérer les utilisateurs approuvés pour référence
try {
    $stmt = $pdo->prepare('
        SELECT u.id, u.username, u.email, u.nom, u.prenom, p.nom as participant_nom, p.prenom as participant_prenom
        FROM users u 
        LEFT JOIN participants p ON u.participant_id = p.id
        WHERE u.approved = TRUE AND u.actif = TRUE 
        ORDER BY u.nom, u.prenom
    ');
    $stmt->execute();
    $approved_users = $stmt->fetchAll();
} catch (Exception $e) {
    $approved_users = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approbation des Utilisateurs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Sys_ges_formation/assets/css/muted.css" rel="stylesheet">
    <style>
        .modal-header {
            background-color: var(--brand);
            color: var(--brand-contrast);
        }
        .btn-approve {
            background-color: #28a745;
            border: none;
        }
        .btn-approve:hover {
            background-color: #218838;
        }
        .btn-reject {
            background-color: #dc3545;
            border: none;
        }
        .btn-reject:hover {
            background-color: #c82333;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>👤 Approbation des Utilisateurs</h1>
                    <a href="/Sys_ges_formation/admin/" class="btn btn-secondary">Retour à l'Admin</a>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Utilisateurs en attente d'approbation -->
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">
                            ⏳ Inscriptions en Attente d'Approbation (<?php echo count($pending_users); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_users)): ?>
                            <p class="text-muted mb-0">✓ Aucune inscription en attente d'approbation</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead style="background-color: var(--brand); color: var(--brand-contrast);">
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Date d'inscription</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_users as $user): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-approve" 
                                                        data-bs-toggle="modal" data-bs-target="#approveModal"
                                                        data-user-id="<?php echo $user['id']; ?>"
                                                        data-user-name="<?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>"
                                                        data-user-email="<?php echo htmlspecialchars($user['email']); ?>">
                                                    ✓ Approuver
                                                </button>
                                                <button type="button" class="btn btn-sm btn-reject" 
                                                        data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                        data-user-id="<?php echo $user['id']; ?>"
                                                        data-user-name="<?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>">
                                                    ✗ Rejeter
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Utilisateurs approuvés -->
                <div class="card">
                    <div class="card-header" style="background-color: var(--brand); color: var(--brand-contrast);">
                        <h5 class="mb-0">
                            ✓ Utilisateurs Approuvés (<?php echo count($approved_users); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($approved_users)): ?>
                            <p class="text-muted mb-0">Aucun utilisateur approuvé</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead style="background-color: #e8e8e8;">
                                        <tr>
                                            <th>Utilisateur</th>
                                            <th>Email</th>
                                            <th>Participant Lié</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($approved_users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php 
                                                if ($user['participant_id']) {
                                                    echo htmlspecialchars($user['participant_prenom'] . ' ' . $user['participant_nom']);
                                                } else {
                                                    echo '<span class="badge bg-warning">Non lié</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'approbation -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approuver l'Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="user_id" id="approveUserId" value="">
                        
                        <p><strong id="approveName"></strong></p>
                        <p class="text-muted">Email: <span id="approveEmail"></span></p>

                        <label for="approveParticipant" class="form-label">
                            Lier à un Participant <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg" id="approveParticipant" name="participant_id" required>
                            <option value="">-- Sélectionner un participant --</option>
                            <?php foreach ($participants as $p): ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?> (<?php echo htmlspecialchars($p['email']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted d-block mt-2">
                            ℹ️ Sélectionnez le participant correspondant à cet utilisateur
                        </small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-approve">✓ Approuver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de rejet -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rejeter l'Inscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="user_id" id="rejectUserId" value="">
                        
                        <p><strong id="rejectName"></strong></p>

                        <label for="rejectReason" class="form-label">Motif du rejet (optionnel)</label>
                        <textarea class="form-control" id="rejectReason" name="rejection_reason" rows="3" 
                                  placeholder="Ex: Email non valide, informations incomplètes..."></textarea>
                        <small class="text-muted d-block mt-2">
                            ℹ️ Ce motif sera montré à l'utilisateur s'il tente de se connecter
                        </small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-reject">✗ Rejeter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Remplir le modal d'approbation
        document.getElementById('approveModal').addEventListener('show.bs.modal', function(e) {
            const button = e.relatedTarget;
            document.getElementById('approveUserId').value = button.getAttribute('data-user-id');
            document.getElementById('approveName').textContent = button.getAttribute('data-user-name');
            document.getElementById('approveEmail').textContent = button.getAttribute('data-user-email');
            document.getElementById('approveParticipant').value = '';
        });

        // Remplir le modal de rejet
        document.getElementById('rejectModal').addEventListener('show.bs.modal', function(e) {
            const button = e.relatedTarget;
            document.getElementById('rejectUserId').value = button.getAttribute('data-user-id');
            document.getElementById('rejectName').textContent = button.getAttribute('data-user-name');
            document.getElementById('rejectReason').value = '';
        });
    </script>
</body>
</html>
