<?php
// Page des résultats du vote
session_start();
require 'db.php';

// Vérifier que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les candidats avec leur nombre de votes
$stmt = $pdo->query("
    SELECT c.id, c.name, c.genre, c.photo, COUNT(v.id) AS total_votes
    FROM candidates c
    LEFT JOIN vote v ON c.id = v.candidate_id
    GROUP BY c.id, c.name, c.genre, c.photo
    ORDER BY total_votes DESC, c.name ASC
");
$candidates = $stmt->fetchAll();

// Inclure la barre de navigation
include 'navbar.php'; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats – Dinner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/navbar.css">
    <link rel="stylesheet" href="style/resultat.css">
</head>
<body>
    <div class="container">
        <div class="resultat-container">
            <h1>Résultats des votes</h1>

            <?php if (empty($candidates)): ?>
                <p class="message info">Aucun candidat n'a encore été enregistré.</p>
            <?php else: ?>
                <?php
                // Calculer le nombre maximum de votes pour déterminer les pourcentages
                $maxVotes = max(array_column($candidates, 'total_votes')) ?: 1;

                // Afficher chaque candidat avec sa barre de progression
                foreach ($candidates as $cand):
                    $pourcentage = round(($cand['total_votes'] / $maxVotes) * 100);
                ?>
                    <div class="candidate">
                        <?php if (!empty($cand['photo'])): ?>
                            <img src="uploads/<?= htmlspecialchars($cand['photo']) ?>" alt="Photo de <?= htmlspecialchars($cand['name']) ?>">
                        <?php else: ?>
                            <div class="no-photo">?</div>
                        <?php endif; ?>
                        <div class="candidate-info">
                            <div class="candidate-name"><?= htmlspecialchars($cand['name']) ?></div>
                            <div class="candidate-genre"><?= $cand['genre'] === 'F' ? 'Candidate (Reine)' : 'Candidat (Roi)' ?></div>
                            <div class="bar-container">
                                <div class="bar" style="width: <?= $pourcentage ?>%">
                                    <?= $cand['total_votes'] ?> vote<?= $cand['total_votes'] > 1 ? 's' : '' ?>
                                </div>
                            </div>
                            <div class="vote-count"><?= $pourcentage ?>% des votes</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Animation des barres de progression au chargement
        document.addEventListener('DOMContentLoaded', () => {
            // Sélectionner toutes les barres
            const bars = document.querySelectorAll('.bar');
            
            // Appliquer une animation progressive
            bars.forEach((bar, index) => {
                // Stocker la largeur originale
                const width = bar.style.width;
                
                // Réinitialiser la largeur à 0
                bar.style.width = '0%';
                
                // Animer progressivement jusqu'à la largeur finale
                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-out';
                    bar.style.width = width;
                }, 100 * index); // Décalage progressif
            });
        });
    </script>
</body>
</html>
