<?php
/**
 * KEMT Center — Interface Administration Messages
 * ------------------------------------------------
 * Accessible sur : /admin/messages.php
 * Protégée par session + mot de passe défini dans /forms/config.php
 */

session_start();
require_once __DIR__ . '/../forms/config.php';

// ── Authentification ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if (
        $_POST['username'] === ADMIN_USER &&
        password_verify($_POST['password'], ADMIN_PASS)
    ) {
        $_SESSION['kemt_admin'] = true;
        $_SESSION['kemt_admin_time'] = time();
        header('Location: messages.php');
        exit;
    } else {
        $loginError = 'Identifiants incorrects.';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: messages.php');
    exit;
}

// Expiration de session après 2h
if (isset($_SESSION['kemt_admin_time']) && (time() - $_SESSION['kemt_admin_time'] > 7200)) {
    session_destroy();
    header('Location: messages.php');
    exit;
}

$isLogged = !empty($_SESSION['kemt_admin']);

// ── Formulaire de login (non connecté) ──────────────────────────────────────
if (!$isLogged): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — KEMT Center</title>
  <link rel="icon" href="../assets/img/kemt_center.png">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'IBM Plex Sans',system-ui,sans-serif;background:#0a1628;min-height:100vh;display:flex;align-items:center;justify-content:center;}
    .login-box{background:#fff;border-radius:16px;padding:3rem 2.5rem;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.4);}
    .logo{text-align:center;margin-bottom:2rem;}
    .logo img{width:60px;height:60px;object-fit:contain;}
    .logo h2{font-size:1.4rem;font-weight:700;color:#0a1628;margin-top:0.8rem;}
    .logo p{font-size:0.8rem;color:#6b7280;letter-spacing:0.05em;text-transform:uppercase;}
    label{display:block;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#6b7280;margin-bottom:0.4rem;margin-top:1.2rem;}
    input{width:100%;padding:0.75rem 1rem;border:1.5px solid #e5e7eb;border-radius:8px;font-size:0.9rem;outline:none;transition:border-color 0.2s;}
    input:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,0.12);}
    .btn-login{display:block;width:100%;margin-top:1.8rem;padding:0.85rem;background:#0a1628;color:#c9a84c;border:none;border-radius:8px;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;cursor:pointer;transition:background 0.2s;}
    .btn-login:hover{background:#c9a84c;color:#0a1628;}
    .error{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:0.7rem 1rem;border-radius:8px;font-size:0.85rem;margin-top:1rem;}
    .back{text-align:center;margin-top:1.5rem;font-size:0.82rem;color:#9ca3af;}
    .back a{color:#c9a84c;text-decoration:none;}
  </style>
</head>
<body>
  <div class="login-box">
    <div class="logo">
      <img src="../assets/img/kemt_center.png" alt="KEMT">
      <h2>KEMT Center</h2>
      <p>Administration</p>
    </div>
    <form method="post">
      <input type="hidden" name="admin_login" value="1">
      <label>Identifiant</label>
      <input type="text" name="username" autocomplete="username" required>
      <label>Mot de passe</label>
      <input type="password" name="password" autocomplete="current-password" required>
      <?php if (!empty($loginError)): ?>
        <div class="error"><?= htmlspecialchars($loginError) ?></div>
      <?php endif; ?>
      <button type="submit" class="btn-login">Se connecter</button>
    </form>
    <div class="back"><a href="../index.html">← Retour au site</a></div>
  </div>
</body>
</html>
<?php exit; endif;

// ── Connexion DB ─────────────────────────────────────────────────────────────
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    die('<div style="padding:2rem;font-family:monospace;color:red;">Connexion DB échouée : '.htmlspecialchars($e->getMessage()).'</div>');
}

// ── Actions (changement de statut, notes) ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_status']) && isset($_POST['msg_id'])) {
        $validStatuses = ['new','read','replied','archived','spam'];
        $st = in_array($_POST['set_status'], $validStatuses) ? $_POST['set_status'] : 'read';
        $st2 = $pdo->prepare("UPDATE contact_messages SET status=?, replied_at=IF(?,NOW(),replied_at) WHERE id=?");
        $st2->execute([$st, $st==='replied', (int)$_POST['msg_id']]);
    }
    if (isset($_POST['save_notes']) && isset($_POST['msg_id'])) {
        $st3 = $pdo->prepare("UPDATE contact_messages SET admin_notes=? WHERE id=?");
        $st3->execute([strip_tags($_POST['admin_notes']), (int)$_POST['msg_id']]);
    }
    header('Location: messages.php' . (isset($_GET['id']) ? '?id='.(int)$_GET['id'] : ''));
    exit;
}

// ── Vue détail d'un message ──────────────────────────────────────────────────
$detail = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $dq = $pdo->prepare("SELECT * FROM contact_messages WHERE id=?");
    $dq->execute([(int)$_GET['id']]);
    $detail = $dq->fetch();
    // Marquer comme lu automatiquement
    if ($detail && $detail['status'] === 'new') {
        $pdo->prepare("UPDATE contact_messages SET status='read' WHERE id=?")->execute([$detail['id']]);
        $detail['status'] = 'read';
    }
}

// ── Liste avec filtres ────────────────────────────────────────────────────────
$filterStatus = $_GET['status'] ?? 'all';
$filterType   = $_GET['type']   ?? 'all';
$search       = trim($_GET['q'] ?? '');
$page         = max(1, (int)($_GET['p'] ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

$where  = [];
$params = [];
if ($filterStatus !== 'all') { $where[] = 'status=?'; $params[] = $filterStatus; }
if ($filterType   !== 'all') { $where[] = 'subject_type=?'; $params[] = $filterType; }
if ($search !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
    $s = '%'.$search.'%';
    $params = array_merge($params, [$s,$s,$s,$s]);
}
$whereClause = $where ? 'WHERE '.implode(' AND ', $where) : '';

$total = (int)$pdo->prepare("SELECT COUNT(*) FROM contact_messages $whereClause")->execute($params) ?
         $pdo->query("SELECT COUNT(*) FROM contact_messages $whereClause")->fetchColumn() : 0;
// Correct count query
$cq = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $whereClause");
$cq->execute($params);
$total = (int)$cq->fetchColumn();
$pages = ceil($total / $perPage);

$listQ = $pdo->prepare("SELECT * FROM contact_messages $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$listQ->execute($params);
$messages = $listQ->fetchAll();

// Compteurs par statut
$counts = $pdo->query("SELECT status, COUNT(*) as c FROM contact_messages GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$total_all = array_sum($counts);

// ── Helpers ─────────────────────────────────────────────────────────────────
function statusBadge($s) {
    $map = ['new'=>['#c9a84c','#0a1628','Nouveau'],'read'=>['#3b82f6','#fff','Lu'],
            'replied'=>['#22c55e','#fff','Répondu'],'archived'=>['#9ca3af','#fff','Archivé'],'spam'=>['#ef4444','#fff','Spam']];
    $d = $map[$s] ?? ['#9ca3af','#fff',$s];
    return "<span style='background:{$d[0]};color:{$d[1]};font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:50px;text-transform:uppercase;letter-spacing:0.05em;'>{$d[2]}</span>";
}
function typeBadge($t) {
    return "<span style='background:rgba(201,168,76,0.15);color:#b8933b;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:3px;text-transform:uppercase;'>$t</span>";
}
function timeAgo($dt) {
    $diff = time() - strtotime($dt);
    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return round($diff/60).'min';
    if ($diff < 86400) return round($diff/3600).'h';
    if ($diff < 604800) return round($diff/86400).'j';
    return date('d/m/Y', strtotime($dt));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Messages — KEMT Admin</title>
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
    .nav-badge{margin-left:auto;background:#c9a84c;color:#0a1628;font-size:0.65rem;font-weight:800;padding:1px 6px;border-radius:50px;}
    .sidebar-footer{margin-top:auto;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.1);}

    /* Main */
    .main-wrap{margin-left:240px;min-height:100vh;}
    .topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 2rem;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:5;}
    .topbar h1{font-size:1.1rem;font-weight:700;color:#0a1628;}
    .topbar-right{display:flex;align-items:center;gap:1rem;}
    .btn-sm{display:inline-flex;align-items:center;gap:0.3rem;padding:0.4rem 0.9rem;border-radius:6px;font-size:0.8rem;font-weight:600;text-decoration:none;cursor:pointer;border:none;transition:all 0.2s;}
    .btn-primary{background:#0a1628;color:#c9a84c;}
    .btn-primary:hover{background:#c9a84c;color:#0a1628;}
    .btn-danger{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;}
    .btn-danger:hover{background:#dc2626;color:#fff;}
    .btn-outline{background:transparent;color:#6b7280;border:1px solid #d1d5db;}
    .btn-outline:hover{background:#f9fafb;}

    /* Filters */
    .filters{background:#fff;border-bottom:1px solid #e5e7eb;padding:0.75rem 2rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
    .filter-tabs{display:flex;gap:0.3rem;}
    .filter-tab{font-size:0.78rem;font-weight:600;padding:0.35rem 0.85rem;border-radius:50px;text-decoration:none;color:#6b7280;border:1px solid #e5e7eb;transition:all 0.2s;white-space:nowrap;}
    .filter-tab:hover,.filter-tab.active{background:#0a1628;color:#c9a84c;border-color:#0a1628;}
    .search-input{padding:0.4rem 0.85rem;border:1px solid #d1d5db;border-radius:6px;font-size:0.85rem;outline:none;min-width:200px;}
    .search-input:focus{border-color:#c9a84c;box-shadow:0 0 0 2px rgba(201,168,76,0.12);}

    /* Content */
    .content{padding:1.5rem 2rem;}

    /* Table */
    .table-card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.08);overflow:hidden;}
    table{width:100%;border-collapse:collapse;}
    thead tr{background:#f9fafb;}
    th{padding:0.75rem 1rem;text-align:left;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#9ca3af;border-bottom:1px solid #e5e7eb;}
    td{padding:0.85rem 1rem;border-bottom:1px solid #f3f4f6;font-size:0.87rem;vertical-align:top;}
    tr:last-child td{border-bottom:none;}
    tr:hover td{background:#fafafa;}
    tr.is-new td{background:#fffbeb;}
    .msg-name{font-weight:700;color:#0a1628;}
    .msg-email{font-size:0.78rem;color:#6b7280;}
    .msg-subject{font-weight:600;color:#374151;}
    .msg-preview{font-size:0.8rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px;}
    .msg-time{font-size:0.75rem;color:#9ca3af;white-space:nowrap;}
    .msg-link{text-decoration:none;color:inherit;display:block;}
    .msg-link:hover .msg-subject{color:#c9a84c;}
    .empty-state{text-align:center;padding:4rem 1rem;color:#9ca3af;}
    .empty-state i{font-size:2.5rem;display:block;margin-bottom:1rem;}

    /* Detail panel */
    .detail-grid{display:grid;grid-template-columns:1fr 340px;gap:1.5rem;}
    .detail-card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.08);overflow:hidden;}
    .detail-header{padding:1.5rem;border-bottom:1px solid #e5e7eb;}
    .detail-header h2{font-size:1.1rem;font-weight:700;color:#0a1628;margin-bottom:0.4rem;}
    .detail-body{padding:1.5rem;}
    .detail-message{background:#f9fafb;border-radius:8px;padding:1.2rem;font-size:0.9rem;line-height:1.75;color:#374151;white-space:pre-wrap;word-wrap:break-word;}
    .meta-row{display:flex;gap:0.5rem;align-items:flex-start;margin-bottom:0.85rem;font-size:0.85rem;}
    .meta-label{font-weight:700;color:#9ca3af;min-width:100px;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.04em;}
    .meta-value{color:#374151;}
    .meta-value a{color:#c9a84c;text-decoration:none;}
    .form-select{padding:0.4rem 0.7rem;border:1px solid #d1d5db;border-radius:6px;font-size:0.85rem;outline:none;background:#fff;}
    .form-select:focus{border-color:#c9a84c;}
    textarea.notes{width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:8px;font-size:0.85rem;min-height:120px;resize:vertical;font-family:inherit;outline:none;}
    textarea.notes:focus{border-color:#c9a84c;box-shadow:0 0 0 2px rgba(201,168,76,0.12);}

    /* Pagination */
    .pagination{display:flex;gap:0.4rem;justify-content:center;padding:1.5rem;}
    .page-link{padding:0.4rem 0.8rem;border:1px solid #d1d5db;border-radius:6px;font-size:0.82rem;text-decoration:none;color:#374151;transition:all 0.2s;}
    .page-link:hover,.page-link.active{background:#0a1628;color:#c9a84c;border-color:#0a1628;}

    @media(max-width:900px){
      .sidebar{display:none;}
      .main-wrap{margin-left:0;}
      .detail-grid{grid-template-columns:1fr;}
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-logo">
    <img src="../assets/img/kemt_center.png" alt="KEMT">
    <div><span>KEMT Center</span><small>Administration</small></div>
  </div>
  <div class="nav-section">
    <div class="nav-label">Gestion</div>
    <a href="messages.php" class="nav-item <?= (!isset($_GET['status']) && !$detail) ? 'active' : '' ?>">
      <i class="bi bi-envelope-fill"></i> Tous les messages
      <?php if (!empty($counts['new'])): ?><span class="nav-badge"><?= $counts['new'] ?></span><?php endif; ?>
    </a>
    <a href="messages.php?status=new" class="nav-item <?= (($_GET['status'] ?? '') === 'new') ? 'active' : '' ?>">
      <i class="bi bi-bell-fill"></i> Nouveaux
      <?php if (!empty($counts['new'])): ?><span class="nav-badge"><?= $counts['new'] ?></span><?php endif; ?>
    </a>
    <a href="messages.php?status=read" class="nav-item <?= (($_GET['status'] ?? '') === 'read') ? 'active' : '' ?>">
      <i class="bi bi-envelope-open-fill"></i> Lus
      <?php if (!empty($counts['read'])): ?><span class="nav-badge" style="background:rgba(255,255,255,0.2);color:#fff;"><?= $counts['read'] ?></span><?php endif; ?>
    </a>
    <a href="messages.php?status=replied" class="nav-item <?= (($_GET['status'] ?? '') === 'replied') ? 'active' : '' ?>">
      <i class="bi bi-check-circle-fill"></i> Répondus
      <?php if (!empty($counts['replied'])): ?><span class="nav-badge" style="background:rgba(255,255,255,0.2);color:#fff;"><?= $counts['replied'] ?></span><?php endif; ?>
    </a>
    <a href="messages.php?status=archived" class="nav-item <?= (($_GET['status'] ?? '') === 'archived') ? 'active' : '' ?>">
      <i class="bi bi-archive-fill"></i> Archivés
      <?php if (!empty($counts['archived'])): ?><span class="nav-badge" style="background:rgba(255,255,255,0.2);color:#fff;"><?= $counts['archived'] ?></span><?php endif; ?>
    </a>
    <a href="messages.php?status=spam" class="nav-item <?= (($_GET['status'] ?? '') === 'spam') ? 'active' : '' ?>">
      <i class="bi bi-slash-circle-fill"></i> Spam
      <?php if (!empty($counts['spam'])): ?><span class="nav-badge" style="background:#ef4444;color:#fff;"><?= $counts['spam'] ?></span><?php endif; ?>
    </a>
  </div>
  <div class="nav-section">
    <div class="nav-label">Comptes</div>
    <a href="collaborators.php" class="nav-item">
      <i class="bi bi-people-fill"></i> Collaborateurs
    </a>
  </div>
  <div class="nav-section">
    <div class="nav-label">Site</div>
    <a href="../index.html" class="nav-item" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Voir le site</a>
    <a href="../contact.html" class="nav-item" target="_blank"><i class="bi bi-person-lines-fill"></i> Page contact</a>
  </div>
  <div class="sidebar-footer">
    <a href="?logout=1" class="nav-item" style="color:rgba(239,68,68,0.8);">
      <i class="bi bi-box-arrow-left"></i> Déconnexion
    </a>
  </div>
</div>

<!-- Main -->
<div class="main-wrap">
  <div class="topbar">
    <h1>
      <?php if ($detail): ?>
        <a href="messages.php" style="color:#9ca3af;text-decoration:none;font-weight:400;font-size:0.9rem;">← Messages</a>
        &nbsp; Message #<?= $detail['id'] ?>
      <?php else: ?>
        Messages reçus
        <span style="color:#9ca3af;font-weight:400;font-size:0.85rem;"> — <?= $total ?> au total</span>
      <?php endif; ?>
    </h1>
    <div class="topbar-right">
      <a href="../contact.html" class="btn-sm btn-outline" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Site</a>
      <a href="?logout=1" class="btn-sm btn-danger"><i class="bi bi-box-arrow-left"></i> Déconnexion</a>
    </div>
  </div>

  <?php if (!$detail): ?>
  <!-- Filters -->
  <div class="filters">
    <div class="filter-tabs">
      <a href="messages.php" class="filter-tab <?= ($filterStatus==='all'&&!$search)?'active':'' ?>">Tous (<?= $total_all ?>)</a>
      <a href="?status=new"      class="filter-tab <?= $filterStatus==='new'?'active':'' ?>">Nouveaux (<?= $counts['new']??0 ?>)</a>
      <a href="?status=read"     class="filter-tab <?= $filterStatus==='read'?'active':'' ?>">Lus (<?= $counts['read']??0 ?>)</a>
      <a href="?status=replied"  class="filter-tab <?= $filterStatus==='replied'?'active':'' ?>">Répondus (<?= $counts['replied']??0 ?>)</a>
      <a href="?status=archived" class="filter-tab <?= $filterStatus==='archived'?'active':'' ?>">Archivés (<?= $counts['archived']??0 ?>)</a>
      <a href="?status=spam"     class="filter-tab <?= $filterStatus==='spam'?'active':'' ?>">Spam (<?= $counts['spam']??0 ?>)</a>
    </div>
    <form method="get" style="margin-left:auto;">
      <?php if($filterStatus!=='all') echo "<input type='hidden' name='status' value='$filterStatus'>"; ?>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="search-input" placeholder="Rechercher...">
    </form>
  </div>
  <?php endif; ?>

  <div class="content">
  <?php if ($detail): ?>
  <!-- ── Detail View ───────────────────────────────────────────────────────── -->
  <div class="detail-grid">
    <div>
      <div class="detail-card">
        <div class="detail-header">
          <div style="display:flex;align-items:center;gap:0.8rem;flex-wrap:wrap;margin-bottom:0.6rem;">
            <?= statusBadge($detail['status']) ?>
            <?= typeBadge($detail['subject_type']) ?>
            <span style="font-size:0.78rem;color:#9ca3af;">Réf. #<?= $detail['id'] ?> · <?= date('d/m/Y à H:i', strtotime($detail['created_at'])) ?></span>
          </div>
          <h2><?= htmlspecialchars($detail['subject']) ?></h2>
        </div>
        <div class="detail-body">
          <div class="meta-row"><span class="meta-label">De</span><span class="meta-value"><strong><?= htmlspecialchars($detail['name']) ?></strong> &lt;<a href="mailto:<?= htmlspecialchars($detail['email']) ?>"><?= htmlspecialchars($detail['email']) ?></a>&gt;</span></div>
          <?php if ($detail['organization']): ?><div class="meta-row"><span class="meta-label">Institution</span><span class="meta-value"><?= htmlspecialchars($detail['organization']) ?></span></div><?php endif; ?>
          <?php if ($detail['phone']): ?><div class="meta-row"><span class="meta-label">Téléphone</span><span class="meta-value"><a href="tel:<?= htmlspecialchars($detail['phone']) ?>"><?= htmlspecialchars($detail['phone']) ?></a></span></div><?php endif; ?>
          <div class="meta-row" style="margin-top:1.2rem;"><span class="meta-label">Message</span></div>
          <div class="detail-message"><?= htmlspecialchars($detail['message']) ?></div>
        </div>
      </div>
    </div>

    <!-- Sidebar actions -->
    <div style="display:flex;flex-direction:column;gap:1rem;">
      <!-- Statut -->
      <div class="detail-card">
        <div class="detail-header"><h2 style="font-size:0.92rem;">Changer le statut</h2></div>
        <div class="detail-body">
          <form method="post">
            <input type="hidden" name="msg_id" value="<?= $detail['id'] ?>">
            <select name="set_status" class="form-select" style="width:100%;margin-bottom:0.8rem;">
              <?php foreach(['new','read','replied','archived','spam'] as $s): ?>
                <option value="<?=$s?>" <?=$detail['status']===$s?'selected':''?>><?=ucfirst($s)?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sm btn-primary" style="width:100%;justify-content:center;"><i class="bi bi-check-lg"></i> Enregistrer</button>
          </form>
        </div>
      </div>
      <!-- Répondre -->
      <div class="detail-card">
        <div class="detail-header"><h2 style="font-size:0.92rem;">Répondre</h2></div>
        <div class="detail-body">
          <a href="mailto:<?= htmlspecialchars($detail['email']) ?>?subject=Re: <?= rawurlencode($detail['subject']) ?> [Réf. #<?= $detail['id'] ?>]"
             class="btn-sm btn-primary" style="display:flex;justify-content:center;text-decoration:none;">
             <i class="bi bi-reply-fill"></i> Ouvrir dans la messagerie
          </a>
        </div>
      </div>
      <!-- Notes admin -->
      <div class="detail-card">
        <div class="detail-header"><h2 style="font-size:0.92rem;">Notes internes</h2></div>
        <div class="detail-body">
          <form method="post">
            <input type="hidden" name="msg_id" value="<?= $detail['id'] ?>">
            <textarea name="admin_notes" class="notes" placeholder="Notes visibles uniquement par l'équipe admin..."><?= htmlspecialchars($detail['admin_notes'] ?? '') ?></textarea>
            <button type="submit" name="save_notes" value="1" class="btn-sm btn-outline" style="margin-top:0.6rem;width:100%;justify-content:center;"><i class="bi bi-save"></i> Enregistrer les notes</button>
          </form>
        </div>
      </div>
      <!-- Infos techniques -->
      <div class="detail-card">
        <div class="detail-header"><h2 style="font-size:0.92rem;">Infos techniques</h2></div>
        <div class="detail-body" style="font-size:0.78rem;color:#9ca3af;">
          <div style="margin-bottom:0.4rem;"><strong>IP :</strong> <?= htmlspecialchars($detail['ip_address'] ?? 'N/A') ?></div>
          <div style="margin-bottom:0.4rem;"><strong>Reçu le :</strong> <?= date('d/m/Y H:i:s', strtotime($detail['created_at'])) ?></div>
          <?php if ($detail['replied_at']): ?><div><strong>Répondu le :</strong> <?= date('d/m/Y H:i', strtotime($detail['replied_at'])) ?></div><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php else: ?>
  <!-- ── List View ─────────────────────────────────────────────────────────── -->
  <div class="table-card">
    <?php if (empty($messages)): ?>
      <div class="empty-state"><i class="bi bi-inbox"></i>Aucun message trouvé.</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Expéditeur</th>
          <th>Catégorie</th>
          <th>Objet &amp; Aperçu</th>
          <th>Statut</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
        <tr class="<?= $m['status']==='new'?'is-new':'' ?>">
          <td style="font-family:monospace;color:#9ca3af;font-size:0.78rem;">#<?= $m['id'] ?></td>
          <td>
            <a href="?id=<?= $m['id'] ?>" class="msg-link">
              <div class="msg-name"><?= htmlspecialchars($m['name']) ?></div>
              <div class="msg-email"><?= htmlspecialchars($m['email']) ?></div>
            </a>
          </td>
          <td><?= typeBadge(htmlspecialchars($m['subject_type'])) ?></td>
          <td style="max-width:320px;">
            <a href="?id=<?= $m['id'] ?>" class="msg-link">
              <div class="msg-subject"><?= htmlspecialchars($m['subject']) ?></div>
              <div class="msg-preview"><?= htmlspecialchars($m['message']) ?></div>
            </a>
          </td>
          <td><?= statusBadge($m['status']) ?></td>
          <td class="msg-time"><?= timeAgo($m['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php for ($i=1; $i<=$pages; $i++): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['p'=>$i])) ?>" class="page-link <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
  </div><!-- /content -->
</div><!-- /main-wrap -->
</body>
</html>
