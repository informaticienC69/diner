<?php
// Page de profil utilisateur
session_start();
require 'db.php';

// Redirection si non connecté
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$errors = [];

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Vérifier si l'utilisateur est candidat
$stmtCand = $pdo->prepare("SELECT * FROM candidates WHERE user_id = ?");
$stmtCand->execute([$userId]);
$candidate = $stmtCand->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    $params = [];
    
    // Mise à jour du nom d'utilisateur
    if (!empty($_POST['username']) && $_POST['username'] !== $user['username']) {
        // Vérifier si le nom d'utilisateur est déjà pris
        $check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = ? AND id != ?");
        $check->execute([$_POST['username'], $userId]);
        if ($check->fetchColumn() > 0) {
            $errors[] = "Ce nom d'utilisateur est déjà pris.";
        } else {
            $updates[] = "username = ?";
            $params[] = $_POST['username'];
        }
    }
    
    // Mise à jour du numéro de téléphone
    if (!empty($_POST['phone']) && $_POST['phone'] !== $user['phone']) {
        // Vérifier si le numéro est déjà pris
        $check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE phone = ? AND id != ?");
        $check->execute([$_POST['phone'], $userId]);
        if ($check->fetchColumn() > 0) {
            $errors[] = "Ce numéro de téléphone est déjà utilisé.";
        } else {
            $updates[] = "phone = ?";
            $params[] = $_POST['phone'];
        }
    }
    
    // Mise à jour du mot de passe
    if (!empty($_POST['new_password'])) {
        if (empty($_POST['current_password'])) {
            $errors[] = "Le mot de passe actuel est requis pour changer le mot de passe.";
        } else {
            // Vérifier le mot de passe actuel
            if (!password_verify($_POST['current_password'], $user['password'])) {
                $errors[] = "Le mot de passe actuel est incorrect.";
            } else {
                $updates[] = "password = ?";
                $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            }
        }
    }
    
    // Si pas d'erreurs, mettre à jour le profil
    if (empty($errors) && !empty($updates)) {
        $params[] = $userId;
        $sql = "UPDATE user SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($params)) {
            $message = "Profil mis à jour avec succès !";
            // Recharger les informations de l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } else {
            $errors[] = "Erreur lors de la mise à jour du profil.";
        }
    }

    // Traitement de la suppression du compte
    if (isset($_POST['delete_account'])) {
        // Vérifier si l'utilisateur est candidat
        if ($candidate) {
            $errors[] = "Vous ne pouvez pas supprimer votre compte tant que vous êtes candidat. Veuillez d'abord retirer votre candidature.";
        } else {
            // Supprimer les votes de l'utilisateur
            $pdo->prepare("DELETE FROM vote WHERE user_id = ?")->execute([$userId]);
            
            // Supprimer le compte
            $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
            if ($stmt->execute([$userId])) {
                session_destroy();
                header('Location: login.php');
                exit;
            } else {
                $errors[] = "Erreur lors de la suppression du compte.";
            }
        }
    }

    // Traitement de la mise à jour de la photo
    if (isset($_POST['update_photo']) && $candidate) {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = "Format de fichier non supporté. Utilisez JPG, PNG ou GIF.";
            } elseif ($file['size'] > $max_size) {
                $errors[] = "L'image ne doit pas dépasser 5MB.";
            } else {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $upload_path = 'uploads/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Supprimer l'ancienne photo si elle existe
                    if (!empty($candidate['photo'])) {
                        $old_photo = 'uploads/' . $candidate['photo'];
                        if (file_exists($old_photo)) {
                            unlink($old_photo);
                        }
                    }

                    // Mettre à jour la base de données
                    $stmt = $pdo->prepare("UPDATE candidates SET photo = ? WHERE user_id = ?");
                    if ($stmt->execute([$filename, $userId])) {
                        $message = "Photo mise à jour avec succès.";
                        // Recharger les informations du candidat
                        $stmtCand->execute([$userId]);
                        $candidate = $stmtCand->fetch();
                    } else {
                        $errors[] = "Erreur lors de la mise à jour de la photo.";
                        // Supprimer le fichier uploadé en cas d'erreur
                        unlink($upload_path);
                    }
                } else {
                    $errors[] = "Erreur lors du téléchargement de la photo.";
                }
            }
        } else {
            $errors[] = "Veuillez sélectionner une photo.";
        }
    }
}

include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil – Dinner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style/base.css">
    <link rel="stylesheet" href="style/navbar.css">
    <link rel="stylesheet" href="style/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <h1>Mon Profil</h1>
                <p class="subtitle">Gérez vos informations personnelles</p>
            </div>

            <?php if ($message): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= implode("<br>", $errors) ?>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <div class="profile-info">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                    <p class="user-phone"><?= htmlspecialchars($user['phone']) ?></p>
                    <p class="user-date">Inscrit le <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                </div>

                <?php if ($candidate): ?>
                    <div class="candidate-photo-section">
                        <h3>Ma photo de candidat</h3>
                        <div class="current-photo">
                            <?php if (!empty($candidate['photo'])): ?>
                                <img src="uploads/<?= htmlspecialchars($candidate['photo']) ?>" 
                                     alt="Photo de <?= htmlspecialchars($user['username']) ?>" 
                                     class="candidate-photo">
                            <?php else: ?>
                                <div class="no-photo">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form method="POST" enctype="multipart/form-data" class="photo-form">
                            <div class="form-group">
                                <label for="photo" class="photo-label">
                                    <i class="fas fa-camera"></i>
                                    Choisir une nouvelle photo
                                </label>
                                <input type="file" id="photo" name="photo" accept="image/*" class="photo-input">
                                <small class="photo-info">Formats acceptés : JPG, PNG, GIF (max 5MB)</small>
                            </div>
                            <button type="submit" name="update_photo" class="btn-primary">
                                <i class="fas fa-upload"></i>
                                Mettre à jour ma photo
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <form method="post" class="profile-form">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Numéro de téléphone</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password">
                        <small>Laissez vide si vous ne souhaitez pas changer le mot de passe</small>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </form>

                <div class="danger-zone">
                    <h3>Zone dangereuse</h3>
                    <p>La suppression de votre compte est irréversible. Toutes vos données seront définitivement effacées.</p>
                    <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
                        <input type="hidden" name="delete_account" value="1">
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-trash-alt"></i> Supprimer mon compte
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Afficher le nom du fichier sélectionné
        document.getElementById('photo').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                this.nextElementSibling.textContent = `Fichier sélectionné : ${fileName}`;
            }
        });
    </script>
</body>
</html> 