# Index de Documentation

## Démarrage Rapide

### Pour les Impatients
**Fichier:** [QUICKSTART.md](QUICKSTART.md)
- 5 minutes pour démarrer
- Accès rapide aux fonctionnalités principales
- Troubleshooting basique

### Installation Complète
**Fichier:** [INSTALLATION.md](INSTALLATION.md)
- Prérequis détaillés
- Configuration pas à pas
- Checklist de vérification
- Solutions aux problèmes courants

---

## 📖 Documentation Fonctionnelle

### Vue d'Ensemble Générale
**Fichier:** [README.md](README.md)
- Présentation du projet
- Architecture complète
- Structure des fichiers
- Schéma base de données
- Fonctionnalités principales
- Sécurité

### Gestion des Transactions
**Fichier:** [TRANSACTIONS.md](TRANSACTIONS.md)
- Instructions TCL (BEGIN, COMMIT, ROLLBACK)
- Fonctions disponibles
- Exemples d'implémentation
- Scénarios de test
- Bonnes pratiques
- Erreurs à éviter

### Champs Calculés
**Fichier:** [CHAMPS_CALCULES.md](CHAMPS_CALCULES.md)
- Formule note finale
- Exemples de calculs
- Pondération (30/50/20)
- Processus automatique
- Tests de validation
- Performance

---

## Architecture du Projet

### Fichiers Principaux
```
config.php          ← Configuration base de données
functions.php       ← Fonctions utilitaires + TCL
header.php          ← En-tête HTML et navigation
footer.php          ← Pied de page
index.php           ← Tableau de bord (accueil)
database.sql        ← Script base de données
```

### Modules (CRUD)

#### Formations
- [formations/list.php](formations/list.php) - Liste des formations
- [formations/add.php](formations/add.php) - Créer/modifier
- [formations/view.php](formations/view.php) - Voir détails
- [formations/delete.php](formations/delete.php) - Supprimer

#### Participants
- [participants/list.php](participants/list.php) - Liste des participants
- [participants/add.php](participants/add.php) - Créer/modifier
- [participants/view.php](participants/view.php) - Voir détails
- [participants/delete.php](participants/delete.php) - Supprimer

#### Évaluations
- [evaluations/list.php](evaluations/list.php) - Liste des évaluations
- [evaluations/add.php](evaluations/add.php) - Créer/modifier
- [evaluations/view.php](evaluations/view.php) - Voir détails
- [evaluations/delete.php](evaluations/delete.php) - Supprimer

---

## Structure Logique

```
Système de Gestion de Formation
│
├── Dashboard (index.php)
│   ├── Statistiques globales
│   └── Accès rapide aux modules
│
├── Module Formations
│   ├── CRUD complet
│   ├── Transactions TCL
│   └── Suppression en cascade
│
├── Module Participants
│   ├── CRUD complet
│   ├── Transactions TCL
│   ├── Historique formations
│   └── Suppression en cascade
│
└── Module Évaluations
    ├── CRUD complet
    ├── Notes avec calcul automatique
    ├── Formule pondérée (30/50/20)
    ├── Détermination résultat (réussi/échoué)
    ├── Gestion certificats
    └── Transactions TCL
```

---

## Flux de Données

```
Utilisateur
    ↓
Interface Web (Formulaires HTML)
    ↓
PHP (validation + préparation)
    ↓
Transactions TCL
    ├─ BEGIN
    ├─ SQL (INSERT/UPDATE/DELETE)
    ├─ Calculs (note_finale)
    ├─ COMMIT (succès) / ROLLBACK (erreur)
    └─ Réponse utilisateur
    ↓
MySQL Database
    └─ Champs calculés (GENERATED ALWAYS AS)
```

---

## Fonctionnalités par Module

### Formations
Voir liste des formations
Créer nouvelle formation
Modifier formation existante
Supprimer formation (en cascade)
Voir détails avec participants
Filtrer par statut (planifiée, en_cours, terminée, annulée)

### Participants
Voir liste des participants
Créer nouveau participant
Modifier profil participant
Supprimer participant (en cascade)
Voir historique formations
Voir évaluations

### Évaluations
Voir liste des évaluations
Créer évaluation (notes)
Modifier évaluation
Supprimer évaluation
Calcul automatique note finale
Résultat automatique (réussi/échoué)
Gestion certificat
Voir détails complets

---

## Sécurité Implémentée

### Protection SQL Injection
Prepared Statements partout
Aucune concaténation directe

### Validation
Validation HTML5 côté client
Validation PHP côté serveur
Filtrage des entrées

### Intégrité des Données
Transactions ACID
Contraintes de clés étrangères
Suppression en cascade sécurisée
Rollback automatique en erreur

---

## Base de Données

### 4 Tables Principales

| Table | Rôle | Champs Importants |
|-------|------|-------------------|
| **formations** | Stocker formations | titre, instructeur, dates, prix, statut |
| **participants** | Stocker participants | nom, email, téléphone, date_inscription, statut |
| **inscriptions** | Lier formations ↔ participants | participant_id, formation_id, date, statut |
| **evaluations** | Stocker notes | note_devoir, note_test, note_participation, **note_finale** (calculée) |

### Relations
```
formations
    ↓
inscriptions (Many-to-Many via table pivot)
    ↑
participants

inscriptions
    ↓
evaluations
```

### Champ Calculé Principal
```
note_finale = (note_devoir × 0.30) 
            + (note_test × 0.50) 
            + (note_participation × 0.20)

Type: DECIMAL(5,2)
Stockage: STORED (pré-calculé en base)
```

---

## Cas d'Usage

### 1. Créer et Gérer une Formation
```
Formations → + Ajouter → Remplir détails → Enregistrer (COMMIT)
```

### 2. Enregistrer Participants
```
Participants → + Ajouter → Remplir infos → Enregistrer (COMMIT)
```

### 3. Évaluer Participants
```
Évaluations → + Ajouter 
→ Sélectionner Participant/Formation
→ Entrer notes (Devoir, Test, Participation)
→ Note finale calculée automatiquement
→ Résultat déterminé automatiquement
→ Enregistrer (COMMIT)
```

### 4. Modifier Données
```
[Quelconque] → Modifier → Changer valeurs → Enregistrer (UPDATE + COMMIT)
```

### 5. Supprimer Données
```
[Quelconque] → Supprimer → Confirmer 
→ Suppressions en cascade exécutées
→ COMMIT validant toutes les suppressions
```

---

## Points Clés à Retenir

### CRUD
- **Create:** + Ajouter (INSERT + COMMIT)
- **Read:** Voir ou Détails (SELECT)
- **Update:** Modifier (UPDATE + COMMIT)
- **Delete:** Supprimer (DELETE + COMMIT)

### TCL (Transaction Control Language)
- **BEGIN:** Démarre transaction
- **COMMIT:** Valide changements
- **ROLLBACK:** Annule changements

### Formule Calcul
```
Note Finale = (Devoir×30%) + (Test×50%) + (Participation×20%)
```

### Seuil Réussite
```
≥ 12/20 → RÉUSSI 
< 12/20 → ÉCHOUÉ 
```

---

## Par Où Commencer?

### Si vous êtes pressé 
→ Lire [QUICKSTART.md](QUICKSTART.md) (5 minutes)

### Si c'est votre premier déploiement 
→ Lire [INSTALLATION.md](INSTALLATION.md) (30 minutes)

### Si vous voulez comprendre l'architecture 
→ Lire [README.md](README.md) (20 minutes)

### Si vous voulez maitriser les transactions 
→ Lire [TRANSACTIONS.md](TRANSACTIONS.md) (15 minutes)

### Si vous voulez comprendre les calculs 
→ Lire [CHAMPS_CALCULES.md](CHAMPS_CALCULES.md) (15 minutes)

---

##  Checklist Complet

```
INSTALLATION
XAMPP installé
Fichiers copiés
database.sql importé
Apache démarré
MySQL démarré

ACCÈS
http://localhost/Sys_ges_formation/ accessible
Dashboard affiche statistiques
Navigation fonctionnelle

FONCTIONNALITÉS
Créer formation
Créer participant
Ajouter évaluation
Modifier données
Supprimer données
Calculs automatiques

SÉCURITÉ
Prepared Statements en place
Transactions TCL fonctionnelles
Validations en place
Suppressions en cascade sûres
```

---

## Ressources Additionnelles

### Concepts PHP
- Variables, arrays, boucles
- PDO et prepared statements
- Try/catch pour gestion erreurs

### Concepts MySQL
- Créer tables et relations
- Types de données
- Contraintes et clés étrangères
- Colonnes générées
- Transactions ACID

### Concepts Web
- Formulaires HTML
- GET/POST
- Redirection avec header()
- Sessions et cookies (optionnel)

---

## Support et FAQ

### Questions Fréquentes

**Q: Comment ajouter un champ?**
A: Modifier le formulaire + la requête SQL + la table DB

**Q: Comment changer la pondération?**
A: Modifier database.sql (note_finale) et réimporter

**Q: Est-ce sécurisé?**
A: Oui, prepared statements + transactions ACID

**Q: Puis-je l'utiliser en production?**
A: Après ajout authentification + HTTPS recommandé

**Q: Comment ajouter des utilisateurs?**
A: Créer table users + ajouter authentification

---
**Version:** 1.0
**Dernière mise à jour:** 26/12/2025
**Auteur:** Groupe 3
-- Phawens LOUIS-JEAN
-- Ismael LOUIS
-- Wilhem CAZEAU
-- Carl Jessy BAZILE
-- Semy Martin BINCE
