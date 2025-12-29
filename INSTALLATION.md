# Guide d'Installation Complet

## Prérequis

- XAMPP installé (PHP 7.4+, MySQL 5.7+)
- Accès à phpMyAdmin
- Navigateur web moderne (Chrome, Firefox, Edge)

---

## Étapes d'Installation

### ÉTAPE 1: Préparer les fichiers

**Localisation:** `C:\xampp\htdocs\Sys_ges_formation\`

Vous devez avoir les fichiers suivants:
```
config.php                 # Connexion base de données
functions.php              # Fonctions TCL et utilitaires
header.php                 # En-tête HTML
footer.php                 # Pied de page
index.php                  # Tableau de bord
database.sql               # Script base de données
README.md                  # Documentation
TRANSACTIONS.md            # Guide TCL
INSTALLATION.md            # Ce fichier

formations/
   ├── list.php
   ├── add.php
   ├── view.php
   ├── edit.php
   └── delete.php

participants/
   ├── list.php
   ├── add.php
   ├── view.php
   ├── edit.php
   └── delete.php

evaluations/
   ├── list.php
   ├── add.php
   ├── view.php
   ├── edit.php
   └── delete.php
```

---

### ÉTAPE 2: Créer la Base de Données

#### Option A: Via phpMyAdmin (Recommandé)

**Étape 1:** Accéder à phpMyAdmin
```
1. Ouvrir: http://localhost/phpmyadmin
2. Identifiants:
   - Utilisateur: root
   - Mot de passe: (vide)
```

**Étape 2:** Importer le script
```
1. Cliquer sur "Importer" (onglet en haut)
2. Sélectionner fichier: database.sql
3. Cliquer "Exécuter"
4. Base "gestion_formation" créée
5. Tables créées avec données de test
```

**Étape 3:** Vérifier l'import
```
1. Dans la colonne gauche, cliquer sur "gestion_formation"
2. Vérifier les 4 tables:
   formations
   participants
   inscriptions
   evaluations
3. Vérifier les données (quelques formations et participants)
```

#### Option B: Via Ligne de Commande

```bash
# Ouvrir terminal Windows (cmd ou PowerShell)
cd C:\xampp\mysql\bin

# Se connecter à MySQL
mysql -u root -p

# Si aucun mot de passe, juste appuyer Entrée
# Exécuter le script
source C:\xampp\htdocs\Sys_ges_formation\database.sql;

# Vérifier
SHOW DATABASES;
USE gestion_formation;
SHOW TABLES;
```

---

### ÉTAPE 3: Vérifier la Configuration

**Fichier:** `config.php`

```php
define('DB_HOST', 'localhost');   // Serveur MySQL (local)
define('DB_USER', 'root');        // Utilisateur MySQL
define('DB_PASS', '');            // Mot de passe (vide par défaut)
define('DB_NAME', 'gestion_formation'); // Nom base créée
```

**Si vous avez changé les identifiants MySQL:**
- Modifier les valeurs dans `config.php`
- Sauvegarder le fichier

---

### ÉTAPE 4: Démarrer l'Application

**Démarrer XAMPP:**
```
1. Ouvrir XAMPP Control Panel
2. Cliquer "Start" pour Apache
3. Cliquer "Start" pour MySQL
4. Les deux doivent être "Running" (vert)
```

**Accéder à l'application:**
```
1. Ouvrir navigateur
2. Aller à: http://localhost/Sys_ges_formation/
3. Page d'accueil avec tableau de bord
```

---

## Première Utilisation

### Scénario Complet de Test

#### Accueil
```
Voir le tableau de bord
Statistiques affichées:
   - Formations: 3
   - Participants: 4
   - Inscriptions: 5
   - Réussis: 0 (pas d'évaluations encore)
```

#### Consulter une Formation
```
1. Cliquer "Formations" (nav)
2. Voir la liste: "PHP Avancé", "MySQL", "Fullstack"
3. Cliquer sur "PHP Avancé"
4. Voir détails et participants inscrits
```

#### Ajouter un Participant
```
1. Cliquer "Participants" (nav)
2. Cliquer "+ Ajouter un Participant"
3. Remplir:
   - Nom: Duplessis
   - Prénom: Eva
   - Email: eva.duplessis@email.com
   - Téléphone: 0612340000
   - Statut: inscrit
4. Cliquer "Enregistrer"
5. Participant ajouté
6. Redirection vers liste (COMMIT exécuté)
```

#### Évaluer un Participant
```
1. Cliquer "Évaluations" (nav)
2. Cliquer "+ Ajouter une Évaluation"
3. Sélectionner: Alice Dupont - PHP Avancé
4. Entrer notes:
   - Devoir: 15.50
   - Test: 16.00
   - Participation: 17.00
5. Observer:
   Formule affichée: (15.50×30%) + (16×50%) + (17×20%)
6. Cliquer "Enregistrer"
7. Évaluation ajoutée
8. Note finale calculée: 16.15/20
9. Résultat: "réussi"
```

#### Vérifier les Calculs
```
1. Cliquer sur l'évaluation 👁️
2. Voir les 4 cartes:
   📦 Devoir: 15.50
   📦 Test: 16.00
   📦 Participation: 17.00
   📦 Note Finale: 16.15 (CALCULÉE)
3. Formule: (15.50×0.3) + (16×0.5) + (17×0.2) = 16.15
4. ✅ Certificat peut être délivré
```

#### 6️⃣ Tester la Modification
```
1. Aller à Participants
2. Cliquer ✏️ sur "Bob Martin"
3. Changer l'email: bob.martin@updated.com
4. Cliquer "Enregistrer"
5. ✅ Email mis à jour (UPDATE + COMMIT)
6. ✅ Redirection vers liste
```

#### 7️⃣ Tester la Suppression
```
1. Aller à Formations
2. Cliquer 🗑️ sur "Web Development Fullstack"
3. Confirmer suppression
4. ✅ Formation supprimée
5. ✅ Ses inscriptions supprimées (en cascade)
6. ✅ Ses évaluations supprimées (en cascade)
7. ✅ COMMIT validant tous les DELETE ensemble
```

---

## 🐛 Troubleshooting

### ❌ Erreur: "Erreur de connexion"

**Cause:** MySQL n'est pas démarré

**Solution:**
```
1. Ouvrir XAMPP Control Panel
2. Cliquer "Start" pour MySQL
3. Attendre 2-3 secondes
4. Recharger la page (F5)
```

### ❌ Erreur: "Unknown database 'gestion_formation'"

**Cause:** Base de données non créée

**Solution:**
```
1. Ouvrir phpMyAdmin
2. Cliquer "Importer"
3. Sélectionner database.sql
4. Cliquer "Exécuter"
5. Recharger la page
```

### ❌ "Aucun participant trouvé"

**Cause:** Les données de test ne sont pas importées

**Solution:**
```
1. Aller à phpMyAdmin
2. Cliquer sur "gestion_formation" → "participants"
3. Si vide, réimporter database.sql
```

### ❌ Les notes ne se calculent pas

**Cause:** Probablement un problème de navigation entre pages

**Solution:**
```
1. Actualiser la page (F5)
2. Vérifier que MySQL est en cours d'exécution
3. Vérifier la note finale dans phpMyAdmin:
   SELECT * FROM evaluations;
```

---

## 🔐 Configuration de Sécurité

### Recommandations

⚠️ **IMPORTANT:** Pour la production:

1. **Changer le mot de passe MySQL**
   ```
   1. Ouvrir phpMyAdmin
   2. Aller à "Comptes d'utilisateur"
   3. Modifier utilisateur "root"
   4. Définir mot de passe
   5. Mettre à jour config.php
   ```

2. **Valider les entrées** (déjà implémenté)
   ```php
   ✅ Prepared Statements utilisés partout
   ✅ htmlspecialchars() pour affichage
   ✅ Validation HTML5 côté client
   ```

3. **Transactions pour l'intégrité** (implémenté)
   ```php
   ✅ BEGIN TRANSACTION avant chaque opération
   ✅ COMMIT si succès
   ✅ ROLLBACK si erreur
   ```

---

## 📊 Structure de la Base de Données

### Schéma Relationnel

```
formations ────────┐
                   │
              inscriptions ──┬──── participants
                   │        │
              evaluations ──┘
```

### Relations
- **formations ↔ participants** (Many-to-Many via `inscriptions`)
- **inscriptions ↔ evaluations** (One-to-Many)

### Index
- Colonnes de recherche indexées pour performance
- Clés étrangères pour intégrité référentielle
- AUTO_INCREMENT pour les IDs

---

## ✅ Checklist de Configuration

```
□ XAMPP installé (Apache + MySQL)
□ Fichiers copiés à: C:\xampp\htdocs\Sys_ges_formation\
□ database.sql importé
□ 4 tables créées (formations, participants, inscriptions, evaluations)
□ Données de test présentes
□ config.php configuré correctement
□ Apache démarré (vert dans XAMPP)
□ MySQL démarré (vert dans XAMPP)
□ http://localhost/Sys_ges_formation/ accessible
□ Tableau de bord affiche statistiques
□ Module Formations accessible
□ Module Participants accessible
□ Module Évaluations accessible
```

---

## 🎓 Prochaines Étapes

### 1. Explorer l'Application
- ✅ Consulter les données de test
- ✅ Ajouter nouveaux enregistrements
- ✅ Tester les modifications
- ✅ Tester les suppressions

### 2. Étudier le Code
- 📖 Lire README.md pour vue d'ensemble
- 📖 Lire TRANSACTIONS.md pour TCL
- 📖 Examiner formations/add.php pour exemple
- 📖 Comprendre la formule de note finale

### 3. Personnaliser (Optionnel)
- 🎨 Modifier couleurs dans header.php
- 🎨 Ajouter champs supplémentaires
- 🎨 Ajouter validations personnalisées

---

## 📞 Support

**Pour toute question:**
1. Consulter README.md
2. Consulter TRANSACTIONS.md
3. Vérifier phpMyAdmin pour les données
4. Vérifier les logs Apache/MySQL

---

**Installation terminée! Bon développement! 🚀**
