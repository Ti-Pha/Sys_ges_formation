# Système de Gestion de Formation Professionnelle - MVP

## Description
Application MVP complète de gestion de formations professionnelles avec PHP/MySQL incluant:
- Gestion des formations (CRUD)
- Gestion des participants (CRUD)
- Gestion des inscriptions
- Évaluations avec calculs automatiques
- Transactions TCL (BEGIN, COMMIT, ROLLBACK)
- Champs calculés (note finale avec formule pondérée)

---

## Installation

### 1. Prérequis
- XAMPP installé
- PHP 7.4+
- MySQL 5.7+

### 2. Configuration
1. Placer les fichiers dans `C:\xampp\htdocs\Sys_ges_formation\`
2. Importer la base de données:
   - Ouvrir phpMyAdmin (http://localhost/phpmyadmin)
   - Créer une nouvelle base de données (optionnel)
   - Importer le fichier `database.sql`

### 3. Accès à l'application
```
http://localhost/Sys_ges_formation/
```

---

## Structure du Projet

```
Sys_ges_formation/
├── config.php              # Connexion à la base de données
├── functions.php           # Fonctions utilitaires + TCL
├── header.php              # En-tête HTML (navigation)
├── footer.php              # Pied de page
├── index.php               # Tableau de bord
├── database.sql            # Script de création DB
│
├── formations/
│   ├── list.php            # Affichage des formations
│   ├── add.php             # Ajouter/Modifier formation
│   ├── view.php            # Détails formation
│   ├── edit.php            # Redirection édition
│   └── delete.php          # Suppression formation
│
├── participants/
│   ├── list.php            # Affichage des participants
│   ├── add.php             # Ajouter/Modifier participant
│   ├── view.php            # Détails participant
│   ├── edit.php            # Redirection édition
│   └── delete.php          # Suppression participant
│
└── evaluations/
    ├── list.php            # Affichage des évaluations
    ├── add.php             # Ajouter/Modifier évaluation
    ├── view.php            # Détails évaluation
    ├── edit.php            # Redirection édition
    └── delete.php          # Suppression évaluation
```

---

## Base de Données

### Tables principales

#### 1. **formations**
```sql
- id: INT (clé primaire)
- titre: VARCHAR(255)
- description: TEXT
- instructeur: VARCHAR(100)
- date_debut: DATE
- date_fin: DATE
- duree_heures: INT
- nombre_participants: INT
- prix_unitaire: DECIMAL(10,2)
- statut: ENUM(planifiée, en_cours, terminée, annulée)
```

#### 2. **participants**
```sql
- id: INT (clé primaire)
- nom: VARCHAR(100)
- prenom: VARCHAR(100)
- email: VARCHAR(100) UNIQUE
- telephone: VARCHAR(20)
- date_inscription: DATE
- statut: ENUM(inscrit, en_cours, terminé, abandonné)
```

#### 3. **inscriptions**
```sql
- id: INT (clé primaire)
- participant_id: INT (FK)
- formation_id: INT (FK)
- date_inscription: DATETIME
- statut: ENUM(inscrit, actif, complété, abandonne)
```

#### 4. **evaluations**
```sql
- id: INT (clé primaire)
- inscription_id: INT (FK)
- note_devoir: DECIMAL(5,2)
- note_test: DECIMAL(5,2)
- note_participation: DECIMAL(5,2)
- note_finale: DECIMAL(5,2) [CHAMP CALCULÉ]
- resultat: ENUM(réussi, échoué, en_attente)
- certificat_delivre: BOOLEAN
```

---

## Champs Calculés

### Note Finale (Générée automatiquement en base de données)
```
NOTE_FINALE = (Note_Devoir × 30%) + (Note_Test × 50%) + (Note_Participation × 20%)
```

**Exemple:**
- Devoir: 15/20 → 15 × 0.30 = 4.50
- Test: 16/20 → 16 × 0.50 = 8.00
- Participation: 17/20 → 17 × 0.20 = 3.40
- **Note Finale = 15.90/20** 

La formule est intégrée dans MySQL avec `GENERATED ALWAYS AS` pour un calcul automatique et cohérent.

---

## Gestion des Transactions (TCL)

### Implementation dans `functions.php`

```php
// Démarrer une transaction
startTransaction($pdo);

// Valider les changements
commit($pdo);

// Annuler les changements
rollback($pdo);
```

### Exemple d'utilisation (dans add.php)
```php
try {
    // BEGIN TRANSACTION
    if (!startTransaction($pdo)) {
        throw new Exception('Impossible de démarrer');
    }
    
    // Exécuter les opérations
    $stmt = $pdo->prepare('INSERT INTO formations ...');
    $success = $stmt->execute([...]);
    
    if ($success) {
        // COMMIT
        if (!commit($pdo)) {
            throw new Exception('Impossible de commiter');
        }
    } else {
        // ROLLBACK
        rollback($pdo);
        throw new Exception('Erreur opération');
    }
} catch (Exception $e) {
    rollback($pdo);
    // Gestion erreur
}
```

### Scénarios de transaction:
1. **Création formation** → 1 INSERT + 1 COMMIT/ROLLBACK
2. **Suppression formation** → DELETE relations + DELETE formation + 1 COMMIT/ROLLBACK
3. **Modification participant** → 1 UPDATE + 1 COMMIT/ROLLBACK
4. **Ajout évaluation** → 1 INSERT + calcul automatique + 1 COMMIT/ROLLBACK

---

## Fonctionnalités Principales

### Formations
- Créer nouvelle formation
- Voir détails avec participants inscrits
- Modifier formation
- Supprimer (suppression en cascade)
- Statuts: planifiée, en_cours, terminée, annulée

### Participants
- Enregistrer nouveaux participants
- Voir historique formations
- Modifier profil
- Supprimer (suppression en cascade)
- Statuts: inscrit, en_cours, terminé, abandonné

### Évaluations
- Ajouter notes pour chaque participant
- Calcul automatique note finale (30/50/20)
- Détermination automatique du résultat
- Gestion certificat
- Visualisation des notes

### Tableau de Bord
- Statistiques globales
- Nombre de formations
- Nombre de participants
- Nombre d'inscriptions
- Nombre de réussis

---

## Instructions d'Utilisation

### 1. Accueil
- Affichage du tableau de bord avec statistiques
- Accès rapide aux modules

### 2. Gestion Formations
```
Formation → list.php → [add.php | view.php | edit.php | delete.php]
```
- Ajouter: `+ Ajouter une Formation`
- Voir: 👁️ icon
- Modifier: ✏️ icon
- Supprimer: 🗑️ icon

### 3. Gestion Participants
```
Participant → list.php → [add.php | view.php | edit.php | delete.php]
```
- Mêmes opérations que les formations

### 4. Gestion Évaluations
```
Évaluation → list.php → [add.php | view.php | delete.php]
```
- Entrer notes (devoir, test, participation)
- Note finale calculée automatiquement
- Résultat déterminé automatiquement (≥12 = réussi)

---

## Exemple de Flux Complet

### Scénario: Évaluer un participant

1. **Créer une formation**
   - `Formations → + Ajouter` → Remplir détails

2. **Ajouter un participant**
   - `Participants → + Ajouter` → Remplir détails

3. **Inscrire le participant**
   - Fait automatiquement lors de création dans DB

4. **Évaluer**
   - `Évaluations → + Ajouter`
   - Sélectionner: Participant + Formation
   - Entrer notes: Devoir (15), Test (16), Participation (17)
   - Note Finale auto = **15.90/20** 
   - Résultat auto = **réussi** 
   - Cocher "Certificat délivré"
   - **COMMIT**

---

## Sécurité

Utilisation de **Prepared Statements** (protection SQL Injection)
Validation des entrées
Transactions ACID
Gestion des erreurs
Suppression en cascade sécurisée

---

##  Notes Importantes

1. **Champs calculés:** La `note_finale` est définie dans MySQL comme colonne générée (`GENERATED ALWAYS AS`), garantissant le calcul automatique et la cohérence.

2. **Transactions:** Tous les INSERT/UPDATE/DELETE utilisent les mécanismes TCL (BEGIN, COMMIT, ROLLBACK) pour garantir l'intégrité des données.

3. **Suppression en cascade:** Les suppressions suppriment automatiquement les enregistrements liés (ex: supprimer une formation supprime ses inscriptions et évaluations).

4. **Validation:** Les notes sont limitées à 0-20 en base de données avec validation HTML5.

---

##  Interface

- **Framework CSS:** Bootstrap 5
- **Design:** Responsive et moderne
- **Icônes:** Unicode emojis pour une meilleure UX
- **Couleurs:** Gradient violet-indigo pour cohérence visuelle

---

##  Support

Pour toute question ou amélioration, consultez la structure du code ou les commentaires dans chaque fichier PHP.

**Développé avec ❤️ pour la formation professionnelle**
