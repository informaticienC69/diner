<!-- navbar.php - Barre de navigation responsive -->
<nav>
  <a href="index.php" class="nav-brand">Dinner</a>
  <div class="nav-links collapsed">
    <a href="index.php">Vote</a>
    <a href="candidater.php">Candidater</a>
    <a href="profile.php">Profil</a>
    <a href="logout.php">Déconnexion</a>
  </div>
  <button class="nav-toggle" aria-label="Menu">
    <span>☰</span>
  </button>
</nav>

<script>
  // Script pour gérer le menu mobile
  document.querySelector('.nav-toggle').addEventListener('click', () => {
    document.querySelector('.nav-links').classList.toggle('collapsed');
  });
  
  // Fermer le menu quand on clique sur un lien
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 768) {
        document.querySelector('.nav-links').classList.add('collapsed');
      }
    });
  });
  
  // Marquer le lien actif
  document.addEventListener('DOMContentLoaded', () => {
    const currentPath = window.location.pathname;
    const filename = currentPath.substring(currentPath.lastIndexOf('/') + 1);
    
    document.querySelectorAll('.nav-links a').forEach(link => {
      const href = link.getAttribute('href');
      if (href === filename || (filename === '' && href === 'index.php')) {
        link.style.fontWeight = 'bold';
        link.style.color = 'var(--primary-color)';
      }
    });
  });
</script>
