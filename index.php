<?php
// Page principale - Système de vote
session_start();
require 'db.php';

// Redirection vers la page de connexion si l'utilisateur n'est pas connecté
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Vérifier si l'utilisateur est candidat
$stmtCheck = $pdo->prepare("SELECT id FROM candidates WHERE user_id = ?");
$stmtCheck->execute([$_SESSION['user_id']]);
$isCandidate = $stmtCheck->fetch();

// Récupérer les candidats séparés par genre
$stmtMen = $pdo->query("SELECT * FROM candidates WHERE genre = 'H' ORDER BY name");
$candidatesMen = $stmtMen->fetchAll();

$stmtWomen = $pdo->query("SELECT * FROM candidates WHERE genre = 'F' ORDER BY name");
$candidatesWomen = $stmtWomen->fetchAll();

// Inclure la barre de navigation
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Votez – Dinner</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style/base.css">
  <link rel="stylesheet" href="style/navbar.css">
  <link rel="stylesheet" href="style/vote.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
  <div class="container">
    <div class="page-header">
      <h1>Élection du Roi et de la Reine</h1>
      <p class="subtitle">Votez pour vos candidats préférés !</p>
      
      <?php if ($isCandidate): ?>
        <div class="candidate-actions">
          <a href="retirer_candidature.php" class="withdraw-btn">
            <i class="fas fa-user-minus"></i> Retirer ma candidature
          </a>
        </div>
      <?php else: ?>
        <div class="candidate-actions">
          <a href="candidater.php" class="btn">
            <i class="fas fa-user-plus"></i> Devenir candidat(e)
          </a>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="vote-sections">
      <!-- Section des candidats masculins -->
      <div class="vote-section men">
        <div class="section-header">
          <i class="fas fa-crown"></i>
          <h2>Roi</h2>
        </div>
        
        <?php if (empty($candidatesMen)): ?>
          <div class="no-candidates">
            <i class="fas fa-user-slash"></i>
            <p class="message info">Aucun candidat masculin pour le moment.</p>
          </div>
        <?php else: ?>
          <div class="candidates-grid">
            <?php foreach ($candidatesMen as $cand): ?>
              <div class="candidate-card">
                <div class="candidate-photo-container">
                  <?php if (!empty($cand['photo'])): ?>
                    <img src="uploads/<?= htmlspecialchars($cand['photo']) ?>" 
                         alt="Photo de <?= htmlspecialchars($cand['name']) ?>" 
                         class="candidate-photo">
                  <?php else: ?>
                    <div class="no-photo">
                      <i class="fas fa-user"></i>
                    </div>
                  <?php endif; ?>
                  <div class="candidate-overlay">
                    <div class="vote-container">
                      <button class="vote-btn" data-id="<?= htmlspecialchars($cand['id']) ?>">
                        <i class="fas fa-plus"></i>
                      </button>
                      <span class="vote-count" id="count-<?= htmlspecialchars($cand['id']) ?>">0</span>
                    </div>
                  </div>
                </div>
                <div class="candidate-info">
                  <h3><?= htmlspecialchars($cand['name']) ?></h3>
                  <div class="candidate-stats">
                    <span class="vote-label">Votes</span>
                    <span class="vote-number" id="count-<?= htmlspecialchars($cand['id']) ?>">0</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Section des candidates féminines -->
      <div class="vote-section women">
        <div class="section-header">
          <i class="fas fa-crown"></i>
          <h2>Reine</h2>
        </div>
        
        <?php if (empty($candidatesWomen)): ?>
          <div class="no-candidates">
            <i class="fas fa-user-slash"></i>
            <p class="message info">Aucune candidate féminine pour le moment.</p>
          </div>
        <?php else: ?>
          <div class="candidates-grid">
            <?php foreach ($candidatesWomen as $cand): ?>
              <div class="candidate-card">
                <div class="candidate-photo-container">
                  <?php if (!empty($cand['photo'])): ?>
                    <img src="uploads/<?= htmlspecialchars($cand['photo']) ?>" 
                         alt="Photo de <?= htmlspecialchars($cand['name']) ?>" 
                         class="candidate-photo">
                  <?php else: ?>
                    <div class="no-photo">
                      <i class="fas fa-user"></i>
                    </div>
                  <?php endif; ?>
                  <div class="candidate-overlay">
                    <div class="vote-container">
                      <button class="vote-btn" data-id="<?= htmlspecialchars($cand['id']) ?>">
                        <i class="fas fa-plus"></i>
                      </button>
                      <span class="vote-count" id="count-<?= htmlspecialchars($cand['id']) ?>">0</span>
                    </div>
                  </div>
                </div>
                <div class="candidate-info">
                  <h3><?= htmlspecialchars($cand['name']) ?></h3>
                  <div class="candidate-stats">
                    <span class="vote-label">Votes</span>
                    <span class="vote-number" id="count-<?= htmlspecialchars($cand['id']) ?>">0</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Overlay de chargement -->
  <div class="loading-overlay" style="display: none;">
    <div class="loading-spinner"></div>
  </div>

  <!-- Notifications -->
  <div id="notifications"></div>

  <script>
    // Fonction pour afficher les notifications
    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
      `;
      
      document.getElementById('notifications').appendChild(notification);
      
      setTimeout(() => notification.classList.add('show'), 100);
      setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
      }, 3000);
    }

    // Fonction pour actualiser les compteurs de votes
    async function refreshCounts() {
      const loadingOverlay = document.querySelector('.loading-overlay');
      loadingOverlay.style.display = 'flex';
      
      try {
        const res = await fetch('count.php');
        const counts = await res.json();
        
        // Mettre à jour tous les compteurs
        for (let id in counts) {
          const countElements = document.querySelectorAll(`[id="count-${id}"]`);
          countElements.forEach(element => {
            element.textContent = counts[id];
          });
        }
        
        // Vérifier les votes de l'utilisateur actuel
        const voteBtns = document.querySelectorAll('.vote-btn');
        voteBtns.forEach(btn => {
          const id = btn.dataset.id;
          if (counts[`user_voted_${id}`]) {
            btn.classList.add('voted');
            btn.innerHTML = '<i class="fas fa-check"></i>';
          } else {
            btn.classList.remove('voted');
            btn.innerHTML = '<i class="fas fa-plus"></i>';
          }
        });
      } catch (error) {
        console.error('Erreur lors de la récupération des votes:', error);
        showNotification('Erreur lors de la mise à jour des votes', 'error');
      } finally {
        loadingOverlay.style.display = 'none';
      }
    }

    // Au chargement de la page
    window.addEventListener('DOMContentLoaded', () => {
      // Actualiser les compteurs immédiatement
      refreshCounts();

      // Ajouter des écouteurs d'événements sur les boutons de vote
      document.querySelectorAll('.vote-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
          const id = btn.dataset.id;
          const loadingOverlay = document.querySelector('.loading-overlay');
          loadingOverlay.style.display = 'flex';
          
          try {
            const res = await fetch('vote.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id })
            });

            const data = await res.json();
            
            if (data.success) {
              // Mettre à jour l'apparence du bouton
              btn.classList.toggle('voted');
              btn.innerHTML = btn.classList.contains('voted') ? 
                '<i class="fas fa-check"></i>' : 
                '<i class="fas fa-plus"></i>';
              
              // Mettre à jour les compteurs
              const countElements = document.querySelectorAll(`[id="count-${id}"]`);
              countElements.forEach(element => {
                element.textContent = data.count;
                element.classList.add('updated');
                setTimeout(() => element.classList.remove('updated'), 500);
              });
              
              // Afficher la notification
              showNotification(data.message, 'success');
              
              // Actualiser tous les compteurs
              refreshCounts();
            } else {
              showNotification(data.message, 'error');
            }
          } catch (error) {
            console.error('Erreur lors du vote:', error);
            showNotification('Une erreur est survenue pendant le vote', 'error');
          } finally {
            loadingOverlay.style.display = 'none';
          }
        });
      });
    });
  </script>
</body>
</html>
