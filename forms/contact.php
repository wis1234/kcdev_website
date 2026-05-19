<?php
/**
 * KEMT Center — Contact Form Handler
 * -----------------------------------
 * Gère la soumission du formulaire de contact :
 *  1. Validation & sanitisation des données
 *  2. Sauvegarde en base de données MySQL (table `contact_messages`)
 *  3. Envoi email de notification à l'équipe KEMT
 *  4. Email de confirmation automatique à l'expéditeur
 *  5. Retourne une réponse JSON au frontend
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Configuration ───────────────────────────────────────────────────────────
require_once __DIR__ . '/config.php';   // constantes DB + email

// ── Sécurité : méthode POST uniquement ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// ── Protection CSRF simple (origin check) ───────────────────────────────────
$allowedOrigins = [SITE_URL];
$origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
$originOk = false;
foreach ($allowedOrigins as $ao) {
    if (strpos($origin, $ao) === 0) { $originOk = true; break; }
}
// En développement local on est plus permissif
if (!$originOk && !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// ── Rate limiting simple (1 message / 60s par IP) ───────────────────────────
$ip = $_SERVER['REMOTE_ADDR'];

// ── Collecte et sanitisation des champs ────────────────────────────────────
$name         = trim(strip_tags($_POST['name']         ?? ''));
$email        = trim(strip_tags($_POST['email']        ?? ''));
$organization = trim(strip_tags($_POST['organization'] ?? ''));
$phone        = trim(strip_tags($_POST['phone']        ?? ''));
$subjectType  = trim(strip_tags($_POST['subject_type'] ?? 'Autre'));
$subject      = trim(strip_tags($_POST['subject']      ?? ''));
$message      = trim(strip_tags($_POST['message']      ?? ''));

// ── Validation ──────────────────────────────────────────────────────────────
$errors = [];

if (empty($name) || strlen($name) < 2)
    $errors[] = 'Le nom est requis (minimum 2 caractères).';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'Adresse e-mail invalide.';

if (empty($subject) || strlen($subject) < 3)
    $errors[] = "L'objet est requis.";

if (empty($message) || strlen($message) < 10)
    $errors[] = 'Le message est trop court (minimum 10 caractères).';

if (strlen($name) > 150 || strlen($email) > 200 || strlen($subject) > 250 || strlen($message) > 5000)
    $errors[] = 'Un champ dépasse la taille maximale autorisée.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Sujets valides (whitelist) ───────────────────────────────────────────────
$validTypes = ['Collaboration', 'Candidature', 'Publication', 'Formation', 'Partenariat', 'Autre'];
if (!in_array($subjectType, $validTypes)) $subjectType = 'Autre';

// ── Connexion MySQL ─────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('[KEMT Contact] DB connexion échouée : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur. Veuillez réessayer.']);
    exit;
}

// ── Rate limiting en base (1 msg / 60s / IP) ────────────────────────────────
try {
    $rateStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM contact_messages
         WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)"
    );
    $rateStmt->execute([$ip]);
    if ((int)$rateStmt->fetchColumn() >= 1) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Veuillez patienter avant d\'envoyer un nouveau message.']);
        exit;
    }
} catch (PDOException $e) {
    // Si la table n'existe pas encore, on continue sans rate limiting
    error_log('[KEMT Contact] Rate limit check échoué : ' . $e->getMessage());
}

// ── Insertion en base ────────────────────────────────────────────────────────
$msgId = null;
try {
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages
            (name, email, organization, phone, subject_type, subject, message, ip_address, user_agent, status)
        VALUES
            (:name, :email, :organization, :phone, :subject_type, :subject, :message, :ip, :ua, 'new')
    ");
    $stmt->execute([
        ':name'         => $name,
        ':email'        => $email,
        ':organization' => $organization ?: null,
        ':phone'        => $phone        ?: null,
        ':subject_type' => $subjectType,
        ':subject'      => $subject,
        ':message'      => $message,
        ':ip'           => $ip,
        ':ua'           => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
    $msgId = $pdo->lastInsertId();
} catch (PDOException $e) {
    error_log('[KEMT Contact] Insertion échouée : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement. Veuillez réessayer.']);
    exit;
}

// ── Email de notification à l'équipe KEMT ───────────────────────────────────
$notifSubject = "[KEMT Contact #$msgId] [$subjectType] $subject";
$notifBody    = "═══════════════════════════════════════\n";
$notifBody   .= "  NOUVEAU MESSAGE — KEMT CENTER\n";
$notifBody   .= "═══════════════════════════════════════\n\n";
$notifBody   .= "Référence  : #$msgId\n";
$notifBody   .= "Catégorie  : $subjectType\n";
$notifBody   .= "Date       : " . date('d/m/Y H:i') . "\n\n";
$notifBody   .= "── Expéditeur ─────────────────────────\n";
$notifBody   .= "Nom        : $name\n";
$notifBody   .= "Email      : $email\n";
if ($organization) $notifBody .= "Institution: $organization\n";
if ($phone)        $notifBody .= "Téléphone  : $phone\n";
$notifBody   .= "\n── Objet ─────────────────────────────\n";
$notifBody   .= "$subject\n\n";
$notifBody   .= "── Message ────────────────────────────\n";
$notifBody   .= wordwrap($message, 72, "\n", true) . "\n\n";
$notifBody   .= "── Accès Admin ────────────────────────\n";
$notifBody   .= SITE_URL . "/admin/messages.php?id=$msgId\n\n";
$notifBody   .= "IP Source  : $ip\n";
$notifBody   .= "═══════════════════════════════════════\n";

$notifHeaders  = "From: KEMT Contact <" . MAIL_FROM . ">\r\n";
$notifHeaders .= "Reply-To: $name <$email>\r\n";
$notifHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
$notifHeaders .= "X-Mailer: KEMT-ContactForm/2.0\r\n";

mail(MAIL_TO, $notifSubject, $notifBody, $notifHeaders);

// ── Email de confirmation à l'expéditeur ────────────────────────────────────
$confirmSubject = "Votre message a bien été reçu — KEMT Center [#$msgId]";
$confirmBody    = "Bonjour $name,\n\n";
$confirmBody   .= "Merci pour votre message. L'équipe du KEMT Center l'a bien reçu\n";
$confirmBody   .= "et vous répondra dans les 48 heures ouvrables.\n\n";
$confirmBody   .= "── Récapitulatif de votre demande ──────\n";
$confirmBody   .= "Référence  : #$msgId\n";
$confirmBody   .= "Catégorie  : $subjectType\n";
$confirmBody   .= "Objet      : $subject\n";
$confirmBody   .= "Date       : " . date('d/m/Y H:i') . "\n\n";
$confirmBody   .= "── Votre message ───────────────────────\n";
$confirmBody   .= wordwrap($message, 72, "\n", true) . "\n\n";
$confirmBody   .= "────────────────────────────────────────\n";
$confirmBody   .= "KEMT Center\n";
$confirmBody   .= "Abomey-Calavi, Bakhita — Bénin\n";
$confirmBody   .= "inquiries@kemtcenter.com | +229 0151 207 640\n";
$confirmBody   .= SITE_URL . "\n\n";
$confirmBody   .= "Cet email est généré automatiquement, veuillez ne pas y répondre.\n";
$confirmBody   .= "Pour toute question, écrivez à inquiries@kemtcenter.com\n";

$confirmHeaders  = "From: KEMT Center <" . MAIL_FROM . ">\r\n";
$confirmHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
$confirmHeaders .= "X-Mailer: KEMT-ContactForm/2.0\r\n";

mail($email, $confirmSubject, $confirmBody, $confirmHeaders);

// ── Réponse succès ───────────────────────────────────────────────────────────
echo json_encode([
    'success'   => true,
    'message'   => 'Message envoyé avec succès.',
    'reference' => '#' . $msgId,
]);
