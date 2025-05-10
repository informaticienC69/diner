<?php
// Page d'inscription
session_start();
require 'db.php';

// Si l'utilisateur est déjà connecté, le rediriger vers la page principale
if (!empty($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

$error = '';
$success = '';
$username = '';
$phone = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Nettoyer le numéro de téléphone (ne garder que les chiffres)
    $phone = preg_replace('/\D/', '', $phone);

    // Vérifier que tous les champs sont remplis
    if ($username === '' || $phone === '' || $password === '' || $password2 === '') {
        $error = 'Tous les champs sont requis.';
    } 
    // Vérifier le format du numéro de téléphone
    elseif (!preg_match('/^(77|78|75|76|70)[0-9]{7}$/', $phone)) {
        $error = 'Numéro invalide. Il doit commencer par 77, 78, 75, 76 ou 70 et contenir exactement 9 chiffres.';
    } 
    // Vérifier que les mots de passe correspondent
    elseif ($password !== $password2) {
        $error = 'Les mots de passe ne correspondent pas.';
    } 
    else {
        // Vérifier si le numéro est déjà utilisé
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Ce numéro est déjà utilisé.';
        } 
        // Vérifier si le nom d'utilisateur est déjà utilisé
        else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Ce nom d\'utilisateur est déjà utilisé.';
            }
            else {
                // Hasher le mot de passe
                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer le nouvel utilisateur
                $ins = $pdo->prepare("INSERT INTO user (username, phone, password) VALUES (?, ?, ?)");
                try {
                    $ins->execute([$username, $phone, $hash]);
                    $success = 'Inscription réussie ! Vous pouvez maintenant vous <a href="login.php">connecter</a>.';
                    // Réinitialiser les champs après une inscription réussie
                    $username = '';
                    $phone = '';
                } catch (PDOException $e) {
                    $error = 'Erreur serveur, réessayez plus tard.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription – Dinner</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style/base.css">
  <link rel="stylesheet" href="style/connexion.css">
</head>
<body>
  <div class="auth-container">
    <h2>Inscription</h2>

    <?php if ($error): ?>
      <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
      <p class="success-message"><?= $success ?></p>
    <?php endif; ?>

    <form method="post" action="">
      <div class="form-group">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" id="username" name="username" placeholder="Votre nom d'utilisateur" required 
               value="<?= htmlspecialchars($username) ?>">
      </div>
      
      <div class="form-group">
        <label for="phone">Numéro de téléphone</label>
        <input type="tel" id="phone" name="phone" placeholder="Ex: 771234567" 
               pattern="^(77|78|75|76|70)[0-9]{7}$" maxlength="9" required 
               value="<?= htmlspecialchars($phone) ?>">
        <small>Format: 77XXXXXXX, 78XXXXXXX, etc.</small>
      </div>
      
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
      </div>
      
      <div class="form-group">
        <label for="password2">Confirmez le mot de passe</label>
        <input type="password" id="password2" name="password2" placeholder="Confirmez votre mot de passe" required>
      </div>
      
      <button type="submit">S'inscrire</button>
    </form>

    <p class="auth-links">Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
  </div>
</body>
</html>
