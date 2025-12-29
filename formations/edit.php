<?php
include '../config.php';
include '../functions.php';
include '../session.php';


requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM formations WHERE id = ?');
    $stmt->execute([$id]);
    $formation = $stmt->fetch();
    
    if (!$formation) {
        header('Location: list.php');
        exit;
    }
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    include '../footer.php';
    exit;
}
// Redirection AVANT d'inclure header.php
header('Location: add.php?id=' . $id);
?>

<div class="mb-3">
    <a href="list.php" class="btn btn-secondary">← Retour</a>
</div>

<h2 class="mb-4">Modifier une Formation</h2>
<form method="GET" action="add.php">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <?php header('Location: add.php?id=' . $id); ?>
</form>

<?php include '../footer.php'; ?>
