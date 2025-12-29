# 🔐 Guide des Transactions TCL (Transaction Control Language)

## Vue d'ensemble

Ce projet implémente les **transactions ACID** à travers les trois instructions TCL principales:
- **BEGIN** - Démarrer une transaction
- **COMMIT** - Valider les changements
- **ROLLBACK** - Annuler les changements

---

## 📋 Fonctions TCL Disponibles

### Dans `functions.php`

#### 1. `startTransaction($pdo)`
**Fonction:** Démarre une nouvelle transaction
```php
if (!startTransaction($pdo)) {
    throw new Exception('Impossible de démarrer la transaction');
}
```

#### 2. `commit($pdo)`
**Fonction:** Valide tous les changements effectués depuis le BEGIN
```php
if (!commit($pdo)) {
    throw new Exception('Impossible de valider la transaction');
}
```

#### 3. `rollback($pdo)`
**Fonction:** Annule tous les changements effectués depuis le BEGIN
```php
if (!rollback($pdo)) {
    throw new Exception('Impossible d\'annuler la transaction');
}
```

---

## 🔄 Cycle de Vie d'une Transaction

```
[START] 
   ↓
[BEGIN TRANSACTION]
   ↓
[EXECUTE SQL COMMANDS]
   ├─→ Succès? → [COMMIT] → Changements sauvegardés ✅
   └─→ Erreur? → [ROLLBACK] → Changements annulés ❌
   ↓
[END]
```

---

## 💻 Exemples d'Implémentation

### Exemple 1: Création d'une Formation

**Fichier:** `formations/add.php`

```php
try {
    // ===== DÉBUT TRANSACTION =====
    if (!startTransaction($pdo)) {
        throw new Exception('Impossible de démarrer la transaction');
    }
    
    // Préparer et exécuter l'INSERT
    $stmt = $pdo->prepare('
        INSERT INTO formations 
        (titre, description, instructeur, date_debut, date_fin, duree_heures, prix_unitaire, statut) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $success = $stmt->execute([
        $titre, 
        $description, 
        $instructeur, 
        $date_debut, 
        $date_fin, 
        $duree_heures, 
        $prix_unitaire, 
        $statut
    ]);
    
    if ($success) {
        // ===== COMMIT =====
        if (!commit($pdo)) {
            throw new Exception('Impossible de valider la transaction');
        }
        // Redirection après succès
        header('Location: list.php');
        exit;
    } else {
        // ===== ROLLBACK =====
        rollback($pdo);
        throw new Exception('Erreur lors de la création');
    }
    
} catch (Exception $e) {
    // Asurer le rollback en cas d'exception
    rollback($pdo);
    $message = getErrorMessage('Erreur: ' . $e->getMessage());
}
```

### Exemple 2: Suppression avec Cascade

**Fichier:** `formations/delete.php`

```php
try {
    // ===== DÉBUT TRANSACTION =====
    if (!startTransaction($pdo)) {
        throw new Exception('Impossible de démarrer la transaction');
    }
    
    // Étape 1: Supprimer les évaluations
    $stmt = $pdo->prepare('
        DELETE FROM evaluations 
        WHERE inscription_id IN (SELECT id FROM inscriptions WHERE formation_id = ?)
    ');
    $stmt->execute([$formation_id]);
    
    // Étape 2: Supprimer les inscriptions
    $stmt = $pdo->prepare('DELETE FROM inscriptions WHERE formation_id = ?');
    $stmt->execute([$formation_id]);
    
    // Étape 3: Supprimer la formation
    $stmt = $pdo->prepare('DELETE FROM formations WHERE id = ?');
    $success = $stmt->execute([$formation_id]);
    
    if ($success && $stmt->rowCount() > 0) {
        // ===== COMMIT =====
        // Tous les DELETE sont validés ensemble
        if (!commit($pdo)) {
            throw new Exception('Impossible de valider la transaction');
        }
        header('Location: list.php?success=deleted');
    } else {
        // ===== ROLLBACK =====
        // Aucune suppression n'est effectuée
        rollback($pdo);
        header('Location: list.php?error=not_found');
    }
    
} catch (Exception $e) {
    // En cas d'erreur, rollback automatique
    rollback($pdo);
    header('Location: list.php?error=' . urlencode($e->getMessage()));
}
```

### Exemple 3: Modification Participant

**Fichier:** `participants/add.php`

```php
try {
    // ===== DÉBUT TRANSACTION =====
    if (!startTransaction($pdo)) {
        throw new Exception('Impossible de démarrer la transaction');
    }
    
    // UPDATE avec vérification des données
    $stmt = $pdo->prepare('
        UPDATE participants 
        SET nom=?, prenom=?, email=?, telephone=?, date_inscription=?, statut=? 
        WHERE id=?
    ');
    
    $success = $stmt->execute([
        $nom, 
        $prenom, 
        $email, 
        $telephone, 
        $date_inscription, 
        $statut, 
        $id
    ]);
    
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
        throw new Exception('Erreur lors de la modification');
    }
    
} catch (Exception $e) {
    rollback($pdo);
    $message = getErrorMessage('Erreur: ' . $e->getMessage());
}
```

---

## 🎯 Points Clés

### ✅ Bonnes Pratiques

1. **Toujours mettre dans un try-catch**
   ```php
   try {
       startTransaction($pdo);
       // ... opérations ...
       commit($pdo);
   } catch (Exception $e) {
       rollback($pdo);
   }
   ```

2. **Vérifier le succès avant commit**
   ```php
   if ($success) {
       commit($pdo);
   } else {
       rollback($pdo);
   }
   ```

3. **Grouper les opérations liées**
   ```php
   // Mauvais: Transaction individuelle pour chaque DELETE
   delete inscriptions;
   delete formations;
   
   // Bon: Une transaction pour toute la suppression
   BEGIN;
   delete evaluations;
   delete inscriptions;
   delete formations;
   COMMIT;
   ```

### ❌ Erreurs à Éviter

1. **Oublier le rollback**
   ```php
   // ❌ MAUVAIS
   if ($success) {
       commit($pdo);
   }
   // Pas de rollback en cas d'erreur!
   
   // ✅ BON
   if ($success) {
       commit($pdo);
   } else {
       rollback($pdo);
   }
   ```

2. **Transaction trop longue**
   ```php
   // ❌ MAUVAIS - Bloque les ressources
   startTransaction();
   // ... 50 opérations ...
   commit();
   
   // ✅ BON - Opérations courtes et précises
   startTransaction();
   // ... 3-5 opérations liées ...
   commit();
   ```

3. **Oublier d'en-tête Location après commit**
   ```php
   // ❌ MAUVAIS
   commit($pdo);
   echo "Succès!";
   
   // ✅ BON
   commit($pdo);
   header('Location: list.php');
   exit;
   ```

---

## 📊 Tableau des Transactions par Module

| Module | Opération | BEGIN | COMMIT | ROLLBACK | Fichier |
|--------|-----------|-------|--------|----------|---------|
| **Formation** | CREATE | ✅ | ✅ | ✅ | add.php |
| **Formation** | UPDATE | ✅ | ✅ | ✅ | add.php |
| **Formation** | DELETE | ✅ | ✅ | ✅ | delete.php |
| **Participant** | CREATE | ✅ | ✅ | ✅ | add.php |
| **Participant** | UPDATE | ✅ | ✅ | ✅ | add.php |
| **Participant** | DELETE | ✅ | ✅ | ✅ | delete.php |
| **Évaluation** | CREATE | ✅ | ✅ | ✅ | add.php |
| **Évaluation** | UPDATE | ✅ | ✅ | ✅ | add.php |
| **Évaluation** | DELETE | ✅ | ✅ | ✅ | delete.php |

---

## 🧪 Scénarios de Test

### Scénario 1: Succès de Transaction
```
1. Remplir formulaire création formation
2. Cliquer "Enregistrer"
3. ✅ Formation créée (COMMIT exécuté)
4. ✅ Redirection vers list.php
```

### Scénario 2: Erreur de Validation
```
1. Laisser champ obligatoire vide
2. Cliquer "Enregistrer"
3. ✅ Erreur affichée
4. ✅ Aucun INSERT exécuté (ROLLBACK)
5. ✅ Formulaire reste actif pour correction
```

### Scénario 3: Suppression en Cascade
```
1. Créer formation (1 formation, 2 participants inscrits, 2 évaluations)
2. Cliquer "Supprimer"
3. ✅ Les 2 évaluations supprimées
4. ✅ Les 2 inscriptions supprimées
5. ✅ La formation supprimée
6. ✅ COMMIT validant les 3 DELETE ensemble
```

### Scénario 4: Modification
```
1. Cliquer "Modifier" sur un participant
2. Changer email
3. Cliquer "Enregistrer"
4. ✅ Email mis à jour (UPDATE + COMMIT)
5. ✅ Redirection vers list.php
```

---

## 🔍 Vérification des Transactions

### Via phpMyAdmin
1. Ouvrir phpMyAdmin
2. Aller à `Bases de données` → `gestion_formation`
3. Cliquer sur `Opérations`
4. Vérifier le type de moteur: **InnoDB** (supporte les transactions)

### Via SQL
```sql
-- Afficher le statut des transactions
SHOW VARIABLES LIKE 'autocommit';

-- Vérifier l'isolation
SHOW VARIABLES LIKE 'transaction_isolation';

-- Afficher les transactions actives
SHOW ENGINE INNODB STATUS;
```

---

## 📈 Avantages des Transactions

| Avantage | Explication |
|----------|-------------|
| **Intégrité** | Tous les changements liés succeèdent ensemble |
| **Cohérence** | Base de données jamais dans état intermédiaire |
| **Isolation** | Transactions concurrentes ne s'interfèrent pas |
| **Durabilité** | COMMIT = changements permanents |
| **Atomicité** | Tout ou rien - pas de modifications partielles |

---

## 🎓 Propriétés ACID

L'application respecte les propriétés ACID grâce aux transactions:

- **Atomicité:** Les opérations groupées (ex: DELETE 3 tables) sont indivisibles
- **Cohérence:** Les états intermédiaires sont impossibles
- **Isolation:** Chaque transaction est indépendante
- **Durabilité:** Les données COMMIT sont permanentes

---

**Version:** 1.0
**Dernière mise à jour:** 26/12/2025
