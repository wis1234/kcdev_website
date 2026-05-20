<?php
/**
 * KEMT Center — Administration Collaborateurs
 * -------------------------------------------
 * Permet à l'admin principal de créer/gérer les accès des chercheurs
 */

session_start();
require_once __DIR__ . '/../forms/config.php';

// Vérification authentification admin
if (empty($_SESSION['kemt_admin'])) {
    header('Location: messages.php');
    exit;
}
if (isset($_SESSION['kemt_admin_time']) && (time() - $_SESSION['kemt_admin_time'] > 7200)) {
    session_destroy();
    header('Location: messages.php');
    exit;
}

// ── Connexion DB ─────────────────────────────────────────────────────────────
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    die('<div style="padding:2rem;font-family:monospace;color:red;">Connexion DB échouée : '.htmlspecialchars($e->getMessage()).'</div>');
}

$successMsg = '';
$errorMsg = '';

// ── Actions ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // AJOUTER UN COLLABORATEUR
    if (isset($_POST['add_collab'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        if (strlen($password) < 6) {
            $errorMsg = "Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO collaborators (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hash, $role]);
                $successMsg = "Le collaborateur <strong>$name</strong> a été ajouté avec succès.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $errorMsg = "Un compte existe déjà avec cette adresse email.";
                } else {
                    $errorMsg = "Erreur lors de l'ajout : " . $e->getMessage();
                }
            }
        }
    }
    
    // MODIFIER STATUT (Actif/Inactif)
    if (isset($_POST['toggle_status'])) {
        $id = (int)$_POST['id'];
        $newStatus = $_POST['new_status'] === 'active' ? 'active' : 'inactive';
        $pdo->prepare("UPDATE collaborators SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        $successMsg = "Statut mis à jour.";
    }

    // SUPPRIMER COLLABORATEUR
    if (isset($_POST['delete_collab'])) {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM collaborators WHERE id = ?")->execute([$id]);
        $successMsg = "Le collaborateur a été supprimé.";
    }
}

// ── Récupérer la liste ────────────────────────────────────────────────────────
$collabs = $pdo->query("SELECT * FROM collaborators ORDER BY created_at DESC")->fetchAll();

// ── Helpers ─────────────────────────────────────────────────────────────────
function roleBadge($r) {
    if ($r === 'admin') return "<span style='background:#fef2f2;color:#dc2626;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:3px;text-transform:uppercase;'>Admin</span>";
    if ($r === 'researcher') return "<span style='background:rgba(201,168,76,0.15);color:#b8933b;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:3px;text-transform:uppercase;'>Chercheur</span>";
    return "<span style='background:#eff6ff;color:#3b82f6;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:3px;text-transform:uppercase;'>Partenaire</span>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Comptes — KEMT Admin</title>
  <link rel="icon" href="../assets/img/kemt_center.png">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'IBM Plex Sans',system-ui,sans-serif;background:#f3f4f6;color:#111827;min-height:100vh;}
    
    /* Sidebar */
    .sidebar{position:fixed;top:0;left:0;width:240px;height:100vh;background:#0a1628;padding:1.5rem 1rem;display:flex;flex-direction:column;z-index:10;}
    .sidebar-logo{display:flex;align-items:center;gap:0.75rem;padding:0 0.5rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.1);}
    .sidebar-logo img{width:36px;height:36px;object-fit:contain;}
    .sidebar-logo span{font-size:0.95rem;font-weight:700;color:#fff;}
    .sidebar-logo small{display:block;font-size:0.68rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.06em;}
    .nav-section{margin-top:1.5rem;}
    .nav-label{font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);padding:0 0.5rem;margin-bottom:0.5rem;}
    .nav-item{display:flex;align-items:center;gap:0.7rem;padding:0.65rem 0.75rem;border-radius:8px;color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.88rem;transition:all 0.2s;margin-bottom:0.25rem;}
    .nav-item:hover,.nav-item.active{background:rgba(201,168,76,0.15);color:#c9a84c;}
    .nav-item i{font-size:1rem;width:18px;text-align:center;}
    
    /* Main */
    .main-wrap{margin-left:240px;min-height:100vh;}
    .topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 2rem;height:60px;display:flex;align-items:center;justify-content:space-between;}
    .topbar h1{font-size:1.1rem;font-weight:700;color:#0a1628;}
    
    /* Content */
    .content{padding:2rem;}
    .grid{display:grid;grid-template-columns:300px 1fr;gap:2rem;}
    
    /* Form Add */
    .card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.08);padding:1.5rem;}
    .card h2{font-size:1rem;font-weight:700;margin-bottom:1rem;color:#0a1628;}
    .form-group{margin-bottom:1rem;}
    label{display:block;font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#6b7280;margin-bottom:0.4rem;}
    input,select{width:100%;padding:0.6rem;border:1px solid #d1d5db;border-radius:6px;font-size:0.85rem;}
    input:focus,select:focus{border-color:#c9a84c;outline:none;}
    .btn{display:inline-flex;align-items:center;gap:0.4rem;padding:0.6rem 1rem;border-radius:6px;font-size:0.85rem;font-weight:600;border:none;cursor:pointer;}
    .btn-primary{background:#0a1628;color:#c9a84c;width:100%;justify-content:center;}
    .btn-primary:hover{background:#c9a84c;color:#0a1628;}
    
    /* Table */
    table{width:100%;border-collapse:collapse;}
    th{text-align:left;padding:0.75rem;font-size:0.72rem;text-transform:uppercase;color:#9ca3af;border-bottom:1px solid #e5e7eb;}
    td{padding:0.85rem 0.75rem;font-size:0.85rem;border-bottom:1px solid #f3f4f6;vertical-align:middle;}
    .status-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:6px;}
    .status-active{background:#22c55e;}
    .status-inactive{background:#dc2626;}
    
    /* Alerts */
    .alert{padding:1rem;border-radius:8px;margin-bottom:1.5rem;font-size:0.85rem;}
    .alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;}
    .alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fca5a5;}
    
    @media(max-width:900px){
      .sidebar{display:none;}
      .main-wrap{margin-left:0;}
      .grid{grid-template-columns:1fr;}
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-logo">
    <img src="../assets/img/kemt_center.png" alt="KEMT">
    <div><span>KEMT Center</span><small>Administration</small></div>
  </div>
  <div class="nav-section">
    <div class="nav-label">Gestion</div>
    <a href="messages.php" class="nav-item"><i class="bi bi-envelope-fill"></i> Messages</a>
  </div>
  <div class="nav-section">
    <div class="nav-label">Comptes</div>
    <a href="collaborators.php" class="nav-item active"><i class="bi bi-people-fill"></i> Collaborateurs</a>
  </div>
  <div class="nav-section" style="margin-top:auto">
    <a href="messages.php?logout=1" class="nav-item" style="color:#ef4444;"><i class="bi bi-box-arrow-left"></i> Déconnexion</a>
  </div>
</div>

<div class="main-wrap">
  <div class="topbar">
    <h1>Gestion des Collaborateurs</h1>
  </div>
  
  <div class="content">
    <?php if($successMsg): ?><div class="alert alert-success"><?= $successMsg ?></div><?php endif; ?>
    <?php if($errorMsg): ?><div class="alert alert-error"><?= $errorMsg ?></div><?php endif; ?>

    <div class="grid">
      <!-- Add Form -->
      <div>
        <div class="card">
          <h2>Nouveau Compte</h2>
          <form method="post">
            <input type="hidden" name="add_collab" value="1">
            <div class="form-group">
              <label>Nom complet</label>
              <input type="text" name="name" required placeholder="Ex: Dr. John Doe">
            </div>
            <div class="form-group">
              <label>Adresse email</label>
              <input type="email" name="email" required placeholder="john@example.edu">
            </div>
            <div class="form-group">
              <label>Mot de passe temporaire</label>
              <input type="text" name="password" required placeholder="Min 6 caractères">
            </div>
            <div class="form-group">
              <label>Rôle</label>
              <select name="role">
                <option value="researcher">Chercheur / Enquêteur</option>
                <option value="partner">Partenaire Institutionnel</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Créer le compte</button>
          </form>
        </div>
      </div>

      <!-- List -->
      <div>
        <div class="card">
          <h2>Comptes existants (<?= count($collabs) ?>)</h2>
          <div style="overflow-x:auto;">
            <table>
              <thead>
                <tr>
                  <th>Utilisateur</th>
                  <th>Rôle</th>
                  <th>Statut</th>
                  <th>Dernière connexion</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($collabs as $c): ?>
                <tr>
                  <td>
                    <div style="font-weight:600;color:#0a1628;"><?= htmlspecialchars($c['name']) ?></div>
                    <div style="font-size:0.75rem;color:#6b7280;"><?= htmlspecialchars($c['email']) ?></div>
                  </td>
                  <td><?= roleBadge($c['role']) ?></td>
                  <td>
                    <div style="display:flex;align-items:center;">
                      <span class="status-dot <?= $c['status']==='active'?'status-active':'status-inactive' ?>"></span>
                      <?= $c['status']==='active' ? 'Actif' : 'Désactivé' ?>
                    </div>
                  </td>
                  <td style="font-size:0.75rem;color:#6b7280;">
                    <?= $c['last_login'] ? date('d/m/Y H:i', strtotime($c['last_login'])) : 'Jamais' ?>
                  </td>
                  <td>
                    <form method="post" style="display:inline-block;margin-right:0.5rem;">
                      <input type="hidden" name="id" value="<?= $c['id'] ?>">
                      <input type="hidden" name="new_status" value="<?= $c['status']==='active'?'inactive':'active' ?>">
                      <button type="submit" name="toggle_status" style="background:none;border:none;color:#6b7280;cursor:pointer;" title="<?= $c['status']==='active'?'Désactiver':'Activer' ?>">
                        <i class="bi <?= $c['status']==='active'?'bi-pause-circle-fill':'bi-play-circle-fill' ?>"></i>
                      </button>
                    </form>
                    <form method="post" style="display:inline-block;" onsubmit="return confirm('Supprimer ce compte définitivement ?');">
                      <input type="hidden" name="id" value="<?= $c['id'] ?>">
                      <button type="submit" name="delete_collab" style="background:none;border:none;color:#ef4444;cursor:pointer;" title="Supprimer">
                        <i class="bi bi-trash-fill"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($collabs)): ?>
                <tr><td colspan="5" style="text-align:center;padding:2rem;color:#9ca3af;">Aucun collaborateur trouvé.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
