# ⚡ Guide Rapide - Quick Start

## 🚀 5 Minutes pour Démarrer

### 1️⃣ Démarrer XAMPP (30 sec)
```
1. XAMPP Control Panel
2. Click "Start" Apache ✅
3. Click "Start" MySQL ✅
```

### 2️⃣ Importer Base de Données (1 min)
```
1. Aller à: http://localhost/phpmyadmin
2. Cliquer "Importer"
3. Sélectionner: database.sql
4. Cliquer "Exécuter" ✅
```

### 3️⃣ Accéder à l'Application (10 sec)
```
http://localhost/Sys_ges_formation/
```

### 4️⃣ Voir les Statistiques
- Formations: 3
- Participants: 4
- Inscriptions: 5
- Réussis: 0

### 5️⃣ Explorer les Modules
- Formations → list.php (voir 3 formations de test)
- Participants → list.php (voir 4 participants de test)
- Évaluations → list.php (ajouter évaluation)

---

## 📱 Actions Principales

### Ajouter une Formation
```
Formations → + Ajouter → Remplir formulaire → Enregistrer
```

### Ajouter un Participant
```
Participants → + Ajouter → Remplir formulaire → Enregistrer
```

### Évaluer un Participant
```
Évaluations → + Ajouter → Sélectionner Participant/Formation
→ Entrer notes (Devoir, Test, Participation)
→ Note finale calculée automatiquement
→ Enregistrer
```

### Modifier Quelque Chose
```
[Liste] → ✏️ Modifier → Changer valeurs → Enregistrer
```

### Supprimer Quelque Chose
```
[Liste] → 🗑️ Supprimer → Confirmer
```

### Voir Détails
```
[Liste] → 👁️ Voir → Affiche toutes les infos
```

---

## 🔢 Formule de Calcul

```
NOTE FINALE = (Devoir × 30%) + (Test × 50%) + (Participation × 20%)

Exemple:
Devoir: 15.50
Test: 16.00
Participation: 17.00

→ (15.50 × 0.3) + (16 × 0.5) + (17 × 0.2) = 16.15/20 ✅
```

---

## 🔐 Transactions TCL

```
Chaque action utilise:
✅ BEGIN TRANSACTION
✅ EXECUTE (INSERT/UPDATE/DELETE)
✅ COMMIT (si succès) ou ROLLBACK (si erreur)
```

**Exemple:**
```
Ajouter une formation:
1. BEGIN
2. INSERT INTO formations ...
3. COMMIT ← Données sauvegardées ✅

Supprimer une formation:
1. BEGIN
2. DELETE FROM evaluations (en cascade)
3. DELETE FROM inscriptions (en cascade)
4. DELETE FROM formations
5. COMMIT ← Les 3 DELETE validés ensemble ✅
```

---

## 📁 Structure Fichiers

```
http://localhost/Sys_ges_formation/
│
├── index.php                 ← Tableau de bord (accueil)
├── config.php                ← Config base de données
├── functions.php             ← Fonctions TCL
│
├── formations/
│   ├── list.php              ← Liste formations
│   ├── add.php               ← Créer/modifier formation
│   ├── view.php              ← Détails formation
│   ├── delete.php            ← Supprimer formation
│
├── participants/
│   ├── list.php              ← Liste participants
│   ├── add.php               ← Créer/modifier participant
│   ├── view.php              ← Détails participant
│   ├── delete.php            ← Supprimer participant
│
├── evaluations/
│   ├── list.php              ← Liste évaluations
│   ├── add.php               ← Créer/modifier évaluation
│   ├── view.php              ← Détails évaluation
│   ├── delete.php            ← Supprimer évaluation
│
└── README.md / INSTALLATION.md / TRANSACTIONS.md / CHAMPS_CALCULES.md
```

---

## 🎯 Cas d'Usage Typique

### Scénario: Gérer une formation

#### Jour 1: Créer Formation
```
1. Formations → + Ajouter
2. Remplir:
   - Titre: "Python Avancé"
   - Instructeur: "Jean Martin"
   - Début: 2025-02-01
   - Fin: 2025-03-01
   - Durée: 40 heures
   - Prix: 600€
3. Enregistrer → ✅ Formation créée
```

#### Jour 2: Inscrire Participants
```
1. Participants → + Ajouter
   - Nom: Dupont
   - Prénom: Alice
   - Email: alice@example.com
2. Enregistrer → ✅ Participant créé

3. Répéter pour ajouter plus de participants
```

#### Fin Formation: Évaluer
```
1. Évaluations → + Ajouter
2. Sélectionner: Alice Dupont - Python Avancé
3. Notes:
   - Devoir: 17/20
   - Test: 18/20
   - Participation: 19/20
4. Enregistrer

Résultat:
✅ Note finale: 18.10/20 (calculée automatiquement)
✅ Résultat: RÉUSSI (≥ 12)
✅ Peut cocher "Certificat délivré"
```

---

## ❌ Problèmes Courants

| Problème | Solution |
|----------|----------|
| "Erreur de connexion" | Démarrer MySQL dans XAMPP |
| "Unknown database" | Importer database.sql |
| Page blanche | Vérifier config.php avec vos identifiants |
| Pas de données | Réimporter database.sql |
| Calculs incorrects | Actualiser la page (F5) |

---

## 🔍 Vérifier que tout marche

### Checklist
```
□ Page d'accueil: http://localhost/Sys_ges_formation/ ✅
□ Tableau de bord affiche 3 formations
□ Tableau de bord affiche 4 participants
□ Tableau de bord affiche 5 inscriptions
□ Module Formations accessible
□ Module Participants accessible
□ Module Évaluations accessible
□ Peut créer une nouvelle formation
□ Peut créer un nouveau participant
□ Peut ajouter une évaluation
□ Note finale se calcule automatiquement
```

---

## 📚 Documentation Complète

Pour plus de détails:
- **README.md** - Vue d'ensemble complet
- **INSTALLATION.md** - Instructions détaillées d'installation
- **TRANSACTIONS.md** - Détails sur BEGIN/COMMIT/ROLLBACK
- **CHAMPS_CALCULES.md** - Détails formule note finale

---

## 🎓 Points Clés à Retenir

1. **CRUD:** Create, Read, Update, Delete tous implémentés
2. **TCL:** Chaque action commence par BEGIN et finit par COMMIT/ROLLBACK
3. **Calcul:** Note finale = (Devoir×0.3) + (Test×0.5) + (Participation×0.2)
4. **Sécurité:** Prepared Statements protègent contre SQL Injection
5. **Cascade:** Supprimer une formation supprime automatiquement ses données liées

---

## 🚀 Vous êtes Prêt!

L'application est prête à l'emploi. Explorez, testez, et amusez-vous! 🎉

**Besoin d'aide?** Consultez les autres fichiers .md 📖
