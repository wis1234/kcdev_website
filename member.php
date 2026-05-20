<?php
session_start();
require_once __DIR__ . '/forms/config.php';

// Si déjà connecté, redirige vers le dashboard
if (!empty($_SESSION['collab_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMsg = "Veuillez remplir tous les champs.";
    } else {
        try {
            $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
            
            $stmt = $pdo->prepare("SELECT * FROM collaborators WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $collab = $stmt->fetch();

            if ($collab && password_verify($password, $collab['password_hash'])) {
                // Succès
                $_SESSION['collab_id'] = $collab['id'];
                $_SESSION['collab_name'] = $collab['name'];
                $_SESSION['collab_role'] = $collab['role'];
                
                // Mettre à jour last_login
                $pdo->prepare("UPDATE collaborators SET last_login = NOW() WHERE id = ?")->execute([$collab['id']]);
                
                header('Location: dashboard.php');
                exit;
            } else {
                $errorMsg = "Identifiants incorrects ou compte inactif.";
            }
        } catch (PDOException $e) {
            $errorMsg = "Erreur de connexion au serveur.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title data-lang-fr="Espace Membre - KEMT Center" data-lang-en="Member Area - KEMT Center">Espace Membre - KEMT Center</title>
  <meta name="description" content="Portail de connexion sécurisé pour les chercheurs, partenaires et experts du KEMT Center.">
  <meta name="keywords" content="espace membre, chercheur, login, partenaires, portail">

  <!-- Favicons -->
  <link href="assets/img/kemt_center.png" rel="icon">
  <link href="assets/img/kemt_center.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

  <!-- Vendor CSS -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  
  <!-- Main CSS -->
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    :root {
      --navy: #0a1628;
      --gold: #c9a84c;
      --gray-100: #f3f4f6;
      --gray-200: #e5e7eb;
      --font-display: 'Playfair Display', serif;
      --font-body: 'IBM Plex Sans', sans-serif;
      --font-mono: 'IBM Plex Mono', monospace;
    }

    body { font-family: var(--font-body); background-color: var(--white); overflow-x: hidden; }
    .portal-header-hide { display: none !important; }

    /* Split Screen Layout */
    .split-layout { display: flex; min-height: 100vh; width: 100%; }
    .split-left {
      flex: 1; background: linear-gradient(135deg, rgba(10,22,40,0.92) 0%, rgba(10,22,40,0.85) 100%), url('assets/img/hero-carousel/hero-carousel-1.jpeg') center/cover no-repeat;
      display: flex; flex-direction: column; justify-content: space-between; padding: 3rem 4rem; color: var(--white); position: relative;
    }
    .split-left::after { content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 1px; background: rgba(201,168,76,0.3); }

    .brand-logo { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: var(--white); }
    .brand-logo img { width: 50px; height: 50px; object-fit: contain; }
    .brand-logo .brand-text { font-family: var(--font-display); font-size: 1.4rem; font-weight: 700; line-height: 1.1; margin: 0; }
    .brand-logo .brand-sub { font-family: var(--font-mono); font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gold); }

    .portal-welcome { max-width: 500px; margin-top: auto; margin-bottom: auto; }
    .portal-welcome h1 { font-family: var(--font-display); font-size: 3rem; font-weight: 700; margin-bottom: 1rem; line-height: 1.2; }
    .portal-welcome p { font-size: 1.05rem; color: rgba(255,255,255,0.7); line-height: 1.6; }
    .gold-line { width: 60px; height: 3px; background: var(--gold); margin-bottom: 1.5rem; }

    .portal-footer { font-family: var(--font-mono); font-size: 0.75rem; color: rgba(255,255,255,0.5); display: flex; gap: 1.5rem; }
    .portal-footer a { color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.3s; }
    .portal-footer a:hover { color: var(--gold); }

    /* Right Side */
    .split-right { flex: 0 0 500px; background: var(--white); display: flex; flex-direction: column; justify-content: center; padding: 4rem; position: relative; }
    
    .btn-back { position: absolute; top: 2rem; right: 2rem; display: inline-flex; align-items: center; gap: 0.4rem; font-family: var(--font-mono); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); text-decoration: none; padding: 0.5rem 1rem; border-radius: 50px; background: var(--gray-100); transition: all 0.3s ease; }
    .btn-back:hover { background: var(--navy); color: var(--gold); }

    .login-wrapper { width: 100%; max-width: 380px; margin: 0 auto; }
    .login-header { margin-bottom: 2.5rem; }
    .login-header h2 { font-family: var(--font-display); font-size: 1.8rem; font-weight: 700; color: var(--navy); margin-bottom: 0.4rem; }
    .login-header p { font-size: 0.9rem; color: var(--gray-500); margin: 0; }

    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--navy); margin-bottom: 0.5rem; }
    .input-wrapper { position: relative; }
    .input-wrapper .form-control { width: 100%; padding: 0.85rem 1rem 0.85rem 2.8rem; border: 1.5px solid var(--gray-200); border-radius: 8px; font-family: var(--font-body); font-size: 0.95rem; color: var(--navy); transition: all 0.3s; background: var(--white); }
    .input-wrapper .form-control:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.12); outline: none; }
    .input-wrapper i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 1.1rem; pointer-events: none; transition: color 0.3s; }
    .input-wrapper:focus-within i { color: var(--gold); }

    .login-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; font-size: 0.85rem; }
    .form-check-label { color: var(--gray-600); cursor: pointer; }
    .form-check-input:checked { background-color: var(--gold); border-color: var(--gold); }
    .forgot-link { color: var(--navy); font-weight: 600; text-decoration: none; transition: color 0.3s; }
    .forgot-link:hover { color: var(--gold); }

    .btn-login { width: 100%; display: flex; justify-content: center; align-items: center; gap: 0.5rem; background: var(--navy); color: var(--gold); font-family: var(--font-mono); font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 1rem; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; }
    .btn-login:hover { background: var(--gold); color: var(--navy); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(201,168,76,0.3); }

    .login-footer { margin-top: 2rem; text-align: center; font-size: 0.85rem; color: var(--gray-500); }
    .login-footer a { color: var(--navy); font-weight: 600; text-decoration: none; border-bottom: 1px solid transparent; transition: all 0.3s; }
    .login-footer a:hover { color: var(--gold); border-bottom-color: var(--gold); }

    .portal-lang { position: absolute; bottom: 2rem; right: 2rem; }
    .lang-btn { background: transparent; border: 1px solid var(--gray-200); color: var(--gray-600); font-family: var(--font-mono); font-size: 0.75rem; padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
    .lang-btn:hover, .lang-btn.active { border-color: var(--gold); color: var(--navy); background: rgba(201,168,76,0.1); }
    .alert-danger { font-size: 0.85rem; padding: 0.8rem; border-radius: 6px; background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; margin-bottom: 1.5rem; text-align: center; }

    @media (max-width: 991px) {
      .split-layout { flex-direction: column; }
      .split-left { padding: 3rem 2rem; min-height: 40vh; flex: none; }
      .split-right { flex: 1; width: 100%; padding: 3rem 2rem; }
      .portal-welcome h1 { font-size: 2.2rem; }
      .btn-back { top: 1rem; right: 1rem; }
      .portal-lang { bottom: auto; top: 1rem; left: 1rem; right: auto; }
    }
  </style>
</head>
<body class="lang-fr">
  <div class="portal-header-hide" data-include="includes/header.html"></div>
  <main class="split-layout">
    
    <div class="split-left">
      <a href="index.html" class="brand-logo">
        <img src="assets/img/kemt_center.png" alt="KEMT Center Logo">
        <div><h2 class="brand-text">KEMT Center</h2><div class="brand-sub">Research &amp; Impact</div></div>
      </a>
      <div class="portal-welcome">
        <div class="gold-line"></div>
        <h1><span data-lang="fr">Portail de Recherche</span><span data-lang="en" class="d-none">Research Portal</span></h1>
        <p><span data-lang="fr">Accédez à l'espace sécurisé réservé aux chercheurs, collaborateurs et partenaires institutionnels du KEMT Center. Retrouvez vos bases de données, protocoles et espaces de travail partagés.</span><span data-lang="en" class="d-none">Access the secure area reserved for researchers, collaborators, and institutional partners of the KEMT Center. Find your databases, protocols, and shared workspaces.</span></p>
      </div>
      <div class="portal-footer">
        <span>&copy; <script>document.write(new Date().getFullYear())</script> KEMT Center</span>
        <a href="contact.html"><span data-lang="fr">Support Technique</span><span data-lang="en" class="d-none">Tech Support</span></a>
      </div>
    </div>

    <div class="split-right">
      <a href="index.html" class="btn-back"><i class="bi bi-arrow-left"></i> <span data-lang="fr">Retour au site</span><span data-lang="en" class="d-none">Back to site</span></a>
      <div class="portal-lang d-flex gap-2">
        <button class="lang-btn active" onclick="switchLanguage('fr')">FR</button>
        <button class="lang-btn" onclick="switchLanguage('en')">EN</button>
      </div>

      <div class="login-wrapper">
        <div class="login-header">
          <h2><span data-lang="fr">Connexion</span><span data-lang="en" class="d-none">Login</span></h2>
          <p><span data-lang="fr">Saisissez vos identifiants pour continuer.</span><span data-lang="en" class="d-none">Enter your credentials to continue.</span></p>
        </div>

        <?php if ($errorMsg): ?>
          <div class="alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <form action="member.php" method="POST">
          <input type="hidden" name="login" value="1">
          <div class="form-group">
            <label class="form-label" for="login-email"><span data-lang="fr">Adresse E-mail</span><span data-lang="en" class="d-none">Email Address</span></label>
            <div class="input-wrapper">
              <input type="email" name="email" class="form-control" id="login-email" placeholder="nom@institution.edu" required>
              <i class="bi bi-envelope"></i>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="login-password"><span data-lang="fr">Mot de passe</span><span data-lang="en" class="d-none">Password</span></label>
            <div class="input-wrapper">
              <input type="password" name="password" class="form-control" id="login-password" placeholder="••••••••" required>
              <i class="bi bi-lock"></i>
            </div>
          </div>
          <div class="login-options">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe">
              <label class="form-check-label" for="rememberMe"><span data-lang="fr">Se souvenir de moi</span><span data-lang="en" class="d-none">Remember me</span></label>
            </div>
            <a href="contact.html" class="forgot-link"><span data-lang="fr">Mot de passe oublié ?</span><span data-lang="en" class="d-none">Forgot password?</span></a>
          </div>
          <button type="submit" class="btn-login">
            <span data-lang="fr">Se connecter</span><span data-lang="en" class="d-none">Sign In</span>
            <i class="bi bi-box-arrow-in-right"></i>
          </button>
        </form>

        <div class="login-footer">
          <span data-lang="fr">Vous n'avez pas de compte ?</span><span data-lang="en" class="d-none">Don't have an account?</span><br>
          <a href="contact.html"><span data-lang="fr">Demander un accès partenaire</span><span data-lang="en" class="d-none">Request partner access</span></a>
        </div>
      </div>
    </div>
  </main>
  
  <div class="portal-header-hide" data-include="includes/footer.html"></div>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/include-html.js"></script>
  <script src="assets/js/lang-switcher.js"></script>
  <script src="assets/js/main.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      window.switchLanguage = function(lang) {
        document.body.className = 'lang-' + lang;
        document.querySelectorAll('.portal-lang .lang-btn').forEach(btn => btn.classList.remove('active'));
        if(lang === 'fr') document.querySelector('.portal-lang .lang-btn:nth-child(1)').classList.add('active');
        else document.querySelector('.portal-lang .lang-btn:nth-child(2)').classList.add('active');
        localStorage.setItem('kemt_lang', lang);
      };
      const currentLang = localStorage.getItem('kemt_lang') || 'fr';
      if(currentLang === 'en') {
        document.querySelector('.portal-lang .lang-btn:nth-child(1)').classList.remove('active');
        document.querySelector('.portal-lang .lang-btn:nth-child(2)').classList.add('active');
      }
    });
  </script>
</body>
</html>
