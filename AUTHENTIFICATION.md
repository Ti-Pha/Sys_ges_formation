# 🔐 Système d'Authentification - Guide d'Utilisation

## Identifiants de Démonstration

### Administrateur
- **Login:** `admin`
- **Mot de passe:** `admin123`
- **Rôle:** Admin (accès complet au système)

### Utilisateur Standard  
- **Login:** `user`
- **Mot de passe:** `user123`
- **Rôle:** User (accès limité)

---

## 📍 Pages Principales

### Page de Connexion
- **URL:** `/Sys_ges_formation/login.php`
- Formulaire d'authentification avec affichage des identifiants de test
- Gestion des messages d'erreur (session expirée, accès non autorisé, etc.)

### Page d'Accueil Publique
- **URL:** `/Sys_ges_formation/` ou `/Sys_ges_formation/index.php`
- Accessible sans authentification
- Affiche les statistiques publiques et les formations à venir
- Lien vers la connexion

---

## 🎯 Dashboards

### Dashboard Admin
- **URL:** `/Sys_ges_formation/admin/index.php`
- **Accessible avec:** Compte admin uniquement
- **Contient:**
  - Statistiques complètes (formations, participants, inscriptions, taux de réussite)
  - Liste des dernières formations créées
  - Liste des évaluations en attente
  - Accès à tous les menus CRUD

### Dashboard Utilisateur
- **URL:** `/Sys_ges_formation/user/index.php`
- **Accessible avec:** Compte utilisateur uniquement
- **Contient:**
  - Statistiques personnelles (formations disponibles, en cours, inscriptions, certificats)
  - Formations populaires
  - Formations à venir
  - Lien vers la liste des formations

---

## 🔒 Sécurité

### Fonctionnalités de Sécurité
- ✅ **Hachage des mots de passe** avec `password_hash()` (bcrypt)
- ✅ **Sessions PHP** avec timeout de 30 minutes
- ✅ **Vérification d'authentification** sur toutes les pages protégées
- ✅ **Vérification du rôle** pour les pages administrateur
- ✅ **Redirection automatique** vers la connexion si non authentifié

### Fonctions de Sécurité (session.php)
```php
isLoggedIn()      // Vérifier si l'utilisateur est connecté
isAdmin()         // Vérifier si l'utilisateur est admin
getCurrentUser()  // Obtenir les infos de l'utilisateur
requireLogin()    // Rediriger si non connecté
requireAdmin()    // Rediriger si non admin
logout()          // Déconnecter l'utilisateur
```

---

## 📊 Structure de la Base de Données

### Table `users`
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,          -- Hachée avec bcrypt
    role ENUM('admin', 'user') DEFAULT 'user',
    nom VARCHAR(100),
    prenom VARCHAR(100),
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 🔄 Flux d'Authentification

```
1. Utilisateur accède à /index.php
   ↓
2. Si connecté → Redirection vers le dashboard approprié (admin ou user)
   Si non connecté → Affichage de la page d'accueil publique
   ↓
3. L'utilisateur clique sur "Se Connecter"
   ↓
4. Accès à /login.php
   ↓
5. Soumission du formulaire avec username et password
   ↓
6. Vérification dans la base de données
   ↓
7. Si valide → Création de la session et redirection vers le dashboard
   Si invalide → Affichage du message d'erreur
```

---

## 🛡️ Protections des Pages

Toutes les pages CRUD sont maintenant protégées :
- **Formations:** Accessible aux admins uniquement
- **Participants:** Accessible aux admins uniquement
- **Évaluations:** Accessible aux admins uniquement
- **Dashboard Admin:** Accessible aux admins uniquement
- **Dashboard Utilisateur:** Accessible aux utilisateurs connectés

Pour ajouter la protection à une page, insérez au début du fichier :
```php
include '../session.php';
requireAdmin();  // Pour restreindre aux admins
// OU
requireLogin();  // Pour restreindre aux utilisateurs connectés
```

---

## 📝 Gestion des Sessions

- **Durée:** 30 minutes d'inactivité
- **Stockage:** Variables `$_SESSION`
- **Informations stockées:**
  - `user_id`
  - `username`
  - `user_email`
  - `user_role`
  - `user_nom`
  - `user_prenom`
  - `last_activity` (timestamp)

---

## 🚀 Prochaines Améliorations

- [ ] Page de profil utilisateur
- [ ] Modification du mot de passe
- [ ] Réinitialisation du mot de passe par email
- [ ] Gestion des utilisateurs (création, modification, suppression)
- [ ] Logs d'activité
- [ ] Authentification à deux facteurs (2FA)
- [ ] Intégration LDAP/OAuth

