<?php
// Page de retrait de candidature
session_start();
require 'db.php';

// Redirection si non connecté
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$message = '';
$userId = $_SESSION['user_id'];

// Vérifier si l'utilisateur est candidat
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE user_id = ?");
$stmt->execute([$userId]);
$candidate = $stmt->fetch();

// Si l'utilisateur n'est pas candidat, le rediriger
if (!$candidate) {
  header('Location: index.php');
  exit;
}

// Traitement de la demande de retrait
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // Démarrer une transaction pour assurer la cohérence des données
    $pdo->beginTransaction();
    
    // Supprimer les votes associés au candidat
    $deleteVotesStmt = $pdo->prepare("DELETE FROM vote WHERE candidate_id = ?");
    $deleteVotesStmt->execute([$candidate['id']]);
    
    // Supprimer la candidature
    $deleteStmt = $pdo->prepare("DELETE FROM candidates WHERE user_id = ?");
    $deleteStmt->execute([$userId]);
    
    // Valider la transaction
    $pdo->commit();
    
    // Supprimer la photo si elle existe
    if (!empty($candidate['photo'])) {
      $photoPath = 'uploads/' . $candidate['photo'];
      if (file_exists($photoPath)) {
        unlink($photoPath);
      }
    }
    
    // Rediriger vers la page principale
    header('Location: index.php');
    exit;
  } catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    $pdo->rollBack();
    $message = "Erreur lors du retrait de candidature : " . $e->getMessage();
  }
}

include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Retirer ma candidature – Dinner</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style/base.css">
  <link rel="stylesheet" href="style/navbar.css">
  <link rel="stylesheet" href="style/candidater.css">
</head>
<body>
  <div class="container">
    <div class="candidater-container">
      <h1>Retirer ma candidature</h1>
      
      <?php if ($message): ?>
        <p class="message warn"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>
      
      <div class="candidate-info">
        <?php if (!empty($candidate['photo'])): ?>
          <img src="uploads/<?= htmlspecialchars($candidate['photo']) ?>" alt="Photo de <?= htmlspecialchars($candidate['name']) ?>" class="candidate-photo">
        <?php endif; ?>
        <h2><?= htmlspecialchars($candidate['name']) ?></h2>
        <p class="candidate-genre"><?= $candidate['genre'] === 'F' ? 'Candidate (Reine)' : 'Candidat (Roi)' ?></p>
      </div>

      <div class="warning-message">
        <p>⚠️ Attention : Cette action est irréversible. En retirant votre candidature :</p>
        <ul>
          <li>Votre profil candidat sera supprimé</li>
          <li>Tous vos votes seront perdus</li>
          <li>Votre photo sera supprimée</li>
        </ul>
      </div>

      <form method="post" class="candidater-form">
        <button type="submit" class="delete-btn">Je confirme le retrait de ma candidature</button>
      </form>

      <a href="index.php" class="back-link">← Retour au vote</a>
    </div>
  </div>
</body>
</html>
