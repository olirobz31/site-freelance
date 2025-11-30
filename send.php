<?php
/**
 * ============================================================
 * FORMULAIRE DE DEVIS - CONFIGURATION EMAIL
 * ============================================================
 * 
 * INSTRUCTIONS POUR LE CLIENT :
 * 
 * 1. Ouvrir ce fichier avec un éditeur de texte (Notepad++, VS Code, etc.)
 * 2. Modifier UNIQUEMENT la section "CONFIGURATION" ci-dessous
 * 3. Sauvegarder le fichier
 * 4. Uploader tous les fichiers sur votre hébergeur via FTP
 * 
 * AIDE : Si vous avez des erreurs, activez le mode DEBUG (ligne 23)
 * ============================================================
 */

// ============================================================
// CONFIGURATION - À MODIFIER PAR LE CLIENT
// ============================================================

// MODE DEBUG - Mettre à true pour voir les erreurs détaillées
define('DEBUG_MODE', false);

// Votre adresse email (où recevoir les demandes)
define('EMAIL_DESTINATAIRE', 'votre-adresse@gmail.com');

// Nom affiché comme expéditeur
define('NOM_EXPEDITEUR', 'Formulaire de Contact');

// ============================================================
// CONFIGURATION SMTP (pour envoi fiable)
// ============================================================
// Choisissez votre méthode d'envoi :
// 'mail' = Fonction PHP mail() standard (peut ne pas fonctionner sur certains hébergeurs)
// 'smtp' = SMTP recommandé pour Gmail, Outlook, etc. (plus fiable)

define('METHODE_ENVOI', 'smtp'); // Changez en 'mail' si vous n'utilisez pas SMTP

// === SI VOUS UTILISEZ SMTP (Gmail, Outlook, etc.) ===
// Pour Gmail : smtp.gmail.com / Port 587 / Créez un "Mot de passe d'application" sur votre compte Google
// Pour Outlook : smtp-mail.outlook.com / Port 587
// Pour OVH : ssl0.ovh.net / Port 587

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-adresse@gmail.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe'); // ⚠️ Mot de passe d'application Gmail (pas votre mot de passe normal)
define('SMTP_ENCRYPTION', 'tls'); // 'tls' ou 'ssl'

// ============================================================
// FIN DE LA CONFIGURATION - NE PAS MODIFIER EN DESSOUS
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérifier si PHPMailer est installé
if (METHODE_ENVOI === 'smtp') {
    if (!file_exists('PHPMailer/src/PHPMailer.php')) {
        echo "METHODE: " . METHODE_ENVOI . " - FILE EXISTS: " . (file_exists('PHPMailer/src/PHPMailer.php') ? 'OUI' : 'NON');
exit;
        die(json_encode([
            'ok' => false, 
            'error' => 'PHPMailer non installé. Téléchargez-le sur github.com/PHPMailer/PHPMailer et placez le dossier "PHPMailer" à côté de send.php'
        ]));
    }
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
}

header('Content-Type: application/json; charset=utf-8');
// Activer les erreurs en mode debug
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Fonction de réponse
function respond($ok, $msg = '') {
    echo json_encode(['ok' => $ok, 'error' => $ok ? '' : $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Méthode non autorisée. Utilisez POST.');
}

// Honeypot anti-spam
if (!empty($_POST['website'])) {
    respond(true); // Bot détecté, on fait semblant que ça a marché
}

// Récupération et validation des champs
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$prestation = trim($_POST['prestation'] ?? '');
$budget = trim($_POST['budget'] ?? '');
$description = trim($_POST['description'] ?? '');

// Validation
if (empty($nom)) respond(false, 'Le nom est requis.');
if (empty($email)) respond(false, 'L\'email est requis.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, 'Email invalide.');
if (empty($prestation)) respond(false, 'Le type de prestation est requis.');
if (empty($budget)) respond(false, 'Le budget est requis.');
if (empty($description)) respond(false, 'La description est requise.');

// Nettoyer les données
$nom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$telephone = htmlspecialchars($telephone, ENT_QUOTES, 'UTF-8');
$prestation = htmlspecialchars($prestation, ENT_QUOTES, 'UTF-8');
$budget = htmlspecialchars($budget, ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

// ============================================================
// ENVOI DE L'EMAIL
// ============================================================

try {
    if (METHODE_ENVOI === 'smtp') {
        // === ENVOI VIA SMTP (PHPMailer) ===
        $mail = new PHPMailer(true);
        
        if (DEBUG_MODE) {
            $mail->SMTPDebug = 2; // Afficher les détails SMTP
        }
        
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        $mail->setFrom(SMTP_USERNAME, NOM_EXPEDITEUR);
        $mail->addAddress(EMAIL_DESTINATAIRE);
        $mail->addReplyTo($email, $nom);
        
        $mail->isHTML(true);
        $mail->Subject = "Nouvelle demande de devis : $prestation";
        $mail->Body    = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #4b79ff, #6ad0ff); color: white; padding: 20px; border-radius: 10px; }
                    .content { background: #f9f9f9; padding: 20px; border-radius: 10px; margin-top: 20px; }
                    .field { margin-bottom: 15px; }
                    .label { font-weight: bold; color: #4b79ff; }
                    .value { margin-top: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2 style='margin:0;'>📬 Nouvelle demande de devis</h2>
                    </div>
                    <div class='content'>
                        <div class='field'>
                            <div class='label'>👤 Nom complet :</div>
                            <div class='value'>$nom</div>
                        </div>
                        <div class='field'>
                            <div class='label'>📧 Email :</div>
                            <div class='value'><a href='mailto:$email'>$email</a></div>
                        </div>
                        <div class='field'>
                            <div class='label'>📞 Téléphone :</div>
                            <div class='value'>" . ($telephone ?: '<em>Non renseigné</em>') . "</div>
                        </div>
                        <div class='field'>
                            <div class='label'>💼 Type de prestation :</div>
                            <div class='value'>$prestation</div>
                        </div>
                        <div class='field'>
                            <div class='label'>💰 Budget estimé :</div>
                            <div class='value'>$budget</div>
                        </div>
                        <div class='field'>
                            <div class='label'>📝 Description du projet :</div>
                            <div class='value'>" . nl2br($description) . "</div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Gestion des pièces jointes
        if (!empty($_FILES['fichiers']['tmp_name'][0])) {
            foreach ($_FILES['fichiers']['tmp_name'] as $i => $tmp) {
                if (is_uploaded_file($tmp)) {
                    $mail->addAttachment($tmp, $_FILES['fichiers']['name'][$i]);
                }
            }
        }
        
        $mail->send();
        respond(true);
        
    } else {
        // === ENVOI VIA mail() PHP STANDARD ===
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: " . NOM_EXPEDITEUR . " <" . EMAIL_DESTINATAIRE . ">\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        $sujet = "Nouvelle demande de devis : $prestation";
        $message = "
            <h2>Nouvelle demande de devis</h2>
            <p><strong>Nom:</strong> $nom</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Téléphone:</strong> $telephone</p>
            <p><strong>Prestation:</strong> $prestation</p>
            <p><strong>Budget:</strong> $budget</p>
            <h3>Description:</h3>
            <p>" . nl2br($description) . "</p>
        ";
        
        if (mail(EMAIL_DESTINATAIRE, $sujet, $message, $headers)) {
            respond(true);
        } else {
            respond(false, 'Échec de l\'envoi. Vérifiez la configuration de votre serveur mail.');
        }
    }
    
} catch (Exception $e) {
    if (DEBUG_MODE) {
        respond(false, 'Erreur détaillée : ' . $e->getMessage());
    } else {
        error_log('Erreur formulaire: ' . $e->getMessage());
        respond(false, 'Échec de l\'envoi. Contactez votre hébergeur ou activez DEBUG_MODE pour plus d\'infos.');
    }
}
?>
 
