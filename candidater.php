<?php
// Page de candidature
session_start();
require 'db.php';

// Redirection si non connecté
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$message = '';
$userId = $_SESSION['user_id'];

// Vérifie si l'utilisateur a déjà candidaté
$check = $pdo->prepare("SELECT COUNT(*) FROM candidates WHERE user_id = ?");
$check->execute([$userId]);
$already = $check->fetchColumn();

// Traitement du formulaire
if ($already > 0) {
  $message = "Tu es déjà candidat(e).";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoName = null;

    // Traitement de la photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
      // Vérifier le type de fichier
      $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
      $fileType = $_FILES['photo']['type'];
      
      if (!in_array($fileType, $allowedTypes)) {
        $message = "Format de fichier non supporté. Veuillez utiliser JPG, PNG ou GIF.";
      } else {
        // Vérifier la taille (max 5MB)
        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
          $message = "La photo est trop volumineuse (max 5MB).";
        } else {
          // Créer le dossier uploads s'il n'existe pas
          if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
          }
          
          // Générer un nom unique pour la photo
          $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
          $photoName = uniqid('photo_') . '.' . strtolower($ext);
          
          // Déplacer le fichier
          if (!move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photoName)) {
            $message = "Erreur lors de l'enregistrement de la photo.";
            $photoName = null;
          }
        }
      }
    } else {
      $message = "Erreur lors de l'envoi de la photo.";
    }
    
    // Si pas d'erreur avec la photo, traiter les autres données
    if (empty($message)) {
      $name = trim($_POST['name'] ?? '');
      $genre = $_POST['genre'] ?? '';

      if ($name === '' || !in_array($genre, ['H', 'F'])) {
        $message = "Tous les champs sont requis, et le genre doit être valide.";
      } else {
        try {
          $stmt = $pdo->prepare("INSERT INTO candidates (name, genre, photo, user_id) VALUES (?, ?, ?, ?)");
          $stmt->execute([$name, $genre, $photoName, $userId]);

          $message = "Ta candidature a bien été enregistrée !";
          $already = true; // empêche un deuxième affichage du formulaire
        } catch (PDOException $e) {
          $message = "Erreur lors de l'enregistrement de la candidature.";
          
          // Supprimer la photo si l'insertion a échoué
          if ($photoName && file_exists('uploads/' . $photoName)) {
            unlink('uploads/' . $photoName);
          }
        }
      }
    }
}

include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Devenir candidat – Dinner</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style/base.css">
  <link rel="stylesheet" href="style/navbar.css">
  <link rel="stylesheet" href="style/candidater.css">
</head>
<body>
  <div class="container">
    <div class="candidater-container">
      <h1>Je me présente comme candidat(e)</h1>

      <?php if ($message): ?>
        <p class="message <?= $already ? 'info' : 'warn' ?>"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>

      <?php if (!$already): ?>
        <form method="post" enctype="multipart/form-data" class="candidater-form">
          <div class="form-group">
            <label for="name">Ton nom ou slogan</label>
            <input type="text" id="name" name="name" placeholder="Ex: Jean le Magnifique" required>
          </div>
          
          <div class="form-group">
            <label for="genre">Ton genre</label>
            <select name="genre" id="genre" required>
              <option value="">-- Sélectionne ton genre --</option>
              <option value="H">Homme</option>
              <option value="F">Femme</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="photo">Ta photo</label>
            <input type="file" id="photo" name="photo" accept="image/*" required>
            <small>Formats acceptés: JPG, PNG, GIF - Max 5MB</small>
          </div>
          
          <button type="submit">Je me porte candidat(e)</button>
        </form>
      <?php endif; ?>

      <a href="index.php" class="back-link">← Retour au vote</a>
    </div>
  </div>
</body>
</html>
