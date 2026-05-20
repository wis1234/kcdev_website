<?php
session_start();
if (empty($_SESSION['collab_id'])) {
    header('Location: member.php');
    exit;
}

$collab_name = $_SESSION['collab_name'] ?? 'Membre';
$collab_role = $_SESSION['collab_role'] ?? 'researcher';

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: member.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title data-lang-fr="Tableau de bord Privé - KEMT Center" data-lang-en="Private Dashboard - KEMT Center">Tableau de bord Privé - KEMT Center</title>
  <meta name="description" content="Espace privé sécurisé pour les collaborateurs du KEMT Center.">
  
  <link href="assets/img/kemt_center.png" rel="icon">
  <link href="assets/img/kemt_center.png" rel="apple-touch-icon">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    :root {
      --navy: #0a1628;
      --gold: #c9a84c;
      --gray-bg: #f8fafc;
    }
    body { background-color: var(--gray-bg); }
    .dashboard-header { background: var(--navy); color: white; padding: 2rem 0; margin-top: 80px; } /* Margin for fixed header */
    .dashboard-title { font-family: 'Playfair Display', serif; font-weight: 700; margin: 0; }
    .dashboard-subtitle { font-family: 'IBM Plex Sans', sans-serif; font-size: 0.9rem; color: var(--gold); text-transform: uppercase; letter-spacing: 0.05em; }
    
    .card-stat { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-top: 3px solid var(--gold); height: 100%; transition: transform 0.3s; }
    .card-stat:hover { transform: translateY(-5px); }
    .card-stat h3 { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 0.5rem; text-transform: uppercase; }
    .card-stat p { font-size: 0.9rem; color: #64748b; margin: 0; }
    
    .private-files { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .file-item { display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-bottom: 1px solid #e2e8f0; }
    .file-item:last-child { border-bottom: none; }
    .file-info { display: flex; align-items: center; gap: 1rem; }
    .file-icon { font-size: 1.5rem; color: var(--navy); }
    .file-name { font-weight: 600; color: var(--navy); font-size: 0.95rem; margin: 0; }
    .file-meta { font-size: 0.8rem; color: #64748b; margin: 0; }
    .btn-download { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 600; padding: 0.4rem 0.8rem; background: rgba(201,168,76,0.1); color: var(--gold); border-radius: 4px; text-decoration: none; transition: background 0.2s; }
    .btn-download:hover { background: var(--gold); color: white; }

    .btn-logout { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: white; padding: 0.4rem 1rem; border-radius: 4px; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; }
    .btn-logout:hover { background: white; color: var(--navy); }
  </style>
</head>
<body class="lang-fr">

  <div data-include="includes/header.html"></div>

  <main class="main">
    <div class="dashboard-header">
      <div class="container d-flex justify-content-between align-items-center">
        <div>
          <div class="dashboard-subtitle"><span data-lang="fr">Espace Privé</span><span data-lang="en" class="d-none">Private Space</span> &bull; <?= htmlspecialchars(strtoupper($collab_role)) ?></div>
          <h1 class="dashboard-title"><span data-lang="fr">Bienvenue,</span><span data-lang="en" class="d-none">Welcome,</span> <?= htmlspecialchars($collab_name) ?></h1>
        </div>
        <div>
          <a href="dashboard.php?logout=1" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i> <span data-lang="fr">Déconnexion</span><span data-lang="en" class="d-none">Logout</span>
          </a>
        </div>
      </div>
    </div>

    <section class="section py-5">
      <div class="container">
        
        <!-- KPIs Row -->
        <div class="row gy-4 mb-5">
          <div class="col-lg-4">
            <div class="card-stat">
              <h3><span data-lang="fr">Publications Actives</span><span data-lang="en" class="d-none">Active Publications</span></h3>
              <div class="fs-2 fw-bold text-dark mb-2">12</div>
              <p><span data-lang="fr">Projets de recherche et working papers.</span><span data-lang="en" class="d-none">Research projects and working papers.</span></p>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card-stat">
              <h3><span data-lang="fr">Impact & Métriques</span><span data-lang="en" class="d-none">Impact & Metrics</span></h3>
              <div class="fs-2 fw-bold text-dark mb-2">+34%</div>
              <p><span data-lang="fr">Engagement des décideurs ce trimestre.</span><span data-lang="en" class="d-none">Policymaker engagement this quarter.</span></p>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card-stat">
              <h3><span data-lang="fr">Accès Données</span><span data-lang="en" class="d-none">Data Access</span></h3>
              <div class="fs-2 fw-bold text-dark mb-2">3</div>
              <p><span data-lang="fr">Bases de données restreintes disponibles.</span><span data-lang="en" class="d-none">Restricted databases available.</span></p>
            </div>
          </div>
        </div>

        <!-- Private Files Area -->
        <div class="row">
          <div class="col-12">
            <div class="private-files">
              <h4 class="mb-4 fw-bold" style="color:var(--navy);"><i class="bi bi-folder2-open me-2"></i> <span data-lang="fr">Ressources & Fichiers Partagés</span><span data-lang="en" class="d-none">Shared Resources & Files</span></h4>
              
              <div class="file-item">
                <div class="file-info">
                  <i class="bi bi-file-earmark-pdf-fill file-icon text-danger"></i>
                  <div>
                    <h5 class="file-name">Rapport_Interne_Q1_2026.pdf</h5>
                    <p class="file-meta">Mis à jour le 15 Mai 2026 &bull; 2.4 MB</p>
                  </div>
                </div>
                <a href="#" class="btn-download" onclick="alert('Téléchargement sécurisé en cours de configuration.'); return false;"><i class="bi bi-cloud-download"></i> <span data-lang="fr">Télécharger</span><span data-lang="en" class="d-none">Download</span></a>
              </div>

              <div class="file-item">
                <div class="file-info">
                  <i class="bi bi-file-earmark-excel-fill file-icon text-success"></i>
                  <div>
                    <h5 class="file-name">Dataset_Covid_Recovery_Clean.xlsx</h5>
                    <p class="file-meta">Mis à jour le 10 Avril 2026 &bull; 14 MB &bull; Restreint</p>
                  </div>
                </div>
                <a href="#" class="btn-download" onclick="alert('Téléchargement sécurisé en cours de configuration.'); return false;"><i class="bi bi-cloud-download"></i> <span data-lang="fr">Télécharger</span><span data-lang="en" class="d-none">Download</span></a>
              </div>

              <div class="file-item">
                <div class="file-info">
                  <i class="bi bi-file-earmark-word-fill file-icon text-primary"></i>
                  <div>
                    <h5 class="file-name">Template_Policy_Brief_KEMT.docx</h5>
                    <p class="file-meta">Standard institutionnel &bull; 45 KB</p>
                  </div>
                </div>
                <a href="#" class="btn-download" onclick="alert('Téléchargement sécurisé en cours de configuration.'); return false;"><i class="bi bi-cloud-download"></i> <span data-lang="fr">Télécharger</span><span data-lang="en" class="d-none">Download</span></a>
              </div>

            </div>
          </div>
        </div>

      </div>
    </section>
  </main>

  <div data-include="includes/footer.html"></div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/include-html.js"></script>
  <script src="assets/js/lang-switcher.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
