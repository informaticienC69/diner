<?php
// Page d'affichage des gagnants
session_start();
require 'db.php';

// Redirection si non connecté
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Récupérer le candidat masculin avec le plus de votes (ROI)
$stmtKing = $pdo->query("
  SELECT c.*, COUNT(v.id) as vote_count 
  FROM candidates c 
  LEFT JOIN vote v ON c.id = v.candidate_id 
  WHERE c.genre = 'H'
  GROUP BY c.id 
  ORDER BY vote_count DESC, c.name ASC
  LIMIT 1
");
$king = $stmtKing->fetch();

// Récupérer la candidate féminine avec le plus de votes (REINE)
$stmtQueen = $pdo->query("
  SELECT c.*, COUNT(v.id) as vote_count 
  FROM candidates c 
  LEFT JOIN vote v ON c.id = v.candidate_id 
  WHERE c.genre = 'F'
  GROUP BY c.id 
  ORDER BY vote_count DESC, c.name ASC
  LIMIT 1
");
$queen = $stmtQueen->fetch();

// Inclure la barre de navigation
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Les Gagnants – Dinner</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style/base.css">
  <link rel="stylesheet" href="style/navbar.css">
  <link rel="stylesheet" href="style/winners.css">
</head>
<body>
  <div class="container">
    <div class="winners-container">
      <h1>Les Gagnants</h1>
      
      <div class="winners-grid">
        <?php if ($king && $king['vote_count'] > 0): ?>
          <div class="winner-card king">
            <div class="crown">👑</div>
            <div class="winner-photo">
              <?php if (!empty($king['photo'])): ?>
                <img src="uploads/<?= htmlspecialchars($king['photo']) ?>" alt="Photo du roi">
              <?php else: ?>
                <div class="no-photo">?</div>
              <?php endif; ?>
            </div>
            <h2>Roi</h2>
            <h3><?= htmlspecialchars($king['name']) ?></h3>
            <div class="vote-count"><?= $king['vote_count'] ?> vote<?= $king['vote_count'] > 1 ? 's' : '' ?></div>
          </div>
        <?php endif; ?>

        <?php if ($queen && $queen['vote_count'] > 0): ?>
          <div class="winner-card queen">
            <div class="crown">👑</div>
            <div class="winner-photo">
              <?php if (!empty($queen['photo'])): ?>
                <img src="uploads/<?= htmlspecialchars($queen['photo']) ?>" alt="Photo de la reine">
              <?php else: ?>
                <div class="no-photo">?</div>
              <?php endif; ?>
            </div>
            <h2>Reine</h2>
            <h3><?= htmlspecialchars($queen['name']) ?></h3>
            <div class="vote-count"><?= $queen['vote_count'] ?> vote<?= $queen['vote_count'] > 1 ? 's' : '' ?></div>
          </div>
        <?php endif; ?>

        <?php if ((!$king || $king['vote_count'] <= 0) && (!$queen || $queen['vote_count'] <= 0)): ?>
          <p class="no-winners">Aucun vote n'a encore été enregistré.</p>
        <?php endif; ?> 
      </div>

      <div class="confetti-container"></div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const confettiContainer = document.querySelector('.confetti-container');
      
      // Créer des confettis
      for (let i = 0; i < 100; i++) {
        createConfetti();
      }

      function createConfetti() {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
        
        // Couleurs aléatoires vives
        const hue = Math.random() * 360;
        confetti.style.backgroundColor = `hsl(${hue}, 100%, 50%)`;
        
        // Formes aléatoires
        const shapes = ['■', '●', '★', '▲', '♦'];
        if (Math.random() > 0.6) {
          confetti.innerHTML = shapes[Math.floor(Math.random() * shapes.length)];
          confetti.style.background = 'none';
          confetti.style.color = `hsl(${hue}, 100%, 50%)`;
          confetti.style.fontSize = '24px';
        }
        
        confettiContainer.appendChild(confetti);

        // Supprimer le confetti après l'animation
        confetti.addEventListener('animationend', () => {
          confetti.remove();
        });
      }
      
      // Ajouter des confettis périodiquement
      setInterval(() => {
        createConfetti();
        createConfetti();
      }, 500);
    });
  </script>
</body>
</html>
