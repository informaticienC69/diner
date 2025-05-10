<?php
// Page de connexion
session_start();
require 'db.php';

// Si l'utilisateur est déjà connecté, le rediriger vers la page principale
if (!empty($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Nettoyer le numéro de téléphone (ne garder que les chiffres)
    $phone = preg_replace('/\D/', '', $phone);

    // Vérifier que tous les champs sont remplis
    if ($phone === '' || $password === '') {
        $error = 'Tous les champs sont requis.';
    } 
    // Vérifier le format du numéro de téléphone
    elseif (!preg_match('/^(77|78|75|76|70)[0-9]{7}$/', $phone)) {
        $error = 'Numéro invalide.';
    } 
    else {
        // Récupérer l'utilisateur correspondant au numéro de téléphone
        $stmt = $pdo->prepare("SELECT id, username, password FROM user WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        // Vérifier le mot de passe
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie, enregistrer les données dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['phone'] = $phone;
            
            // Rediriger vers la page principale
            header('Location: index.php');
            exit;
        } else {
            $error = 'Identifiants invalides.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion – Dinner</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style/base.css">
  <link rel="stylesheet" href="style/connexion.css">
</head>
<body>
  <div class="auth-container">
    <h2>Connexion</h2>
    
    <?php if ($error): ?>
      <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <form method="post" action="">
      <div class="form-group">
        <label for="phone">Numéro de téléphone</label>
        <input type="tel" id="phone" name="phone" placeholder="Ex: 771234567" required 
               pattern="^(77|78|75|76|70)[0-9]{7}$" maxlength="9" 
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        <small>Format: 77XXXXXXX, 78XXXXXXX, etc.</small>
      </div>
      
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
      </div>
      
      <button type="submit">Se connecter</button>
    </form>
    
    <p class="auth-links">Pas encore inscrit ? <a href="inscription.php">Créer un compte</a></p>
  </div>
</body>
</html>
