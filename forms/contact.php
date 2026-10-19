<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=UTF-8');

function contactJsonResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function contactTextLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    contactJsonResponse(405, [
        'success' => false,
        'message' => 'Cette méthode de requête n’est pas autorisée.',
    ]);
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if (
    contactTextLength($name) < 2
    || contactTextLength($name) > 100
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || contactTextLength($email) > 150
    || contactTextLength($subject) < 3
    || contactTextLength($subject) > 150
    || contactTextLength($message) < 10
    || contactTextLength($message) > 5000
    || ($phone !== '' && !preg_match('/^[0-9+\s()\-]{8,30}$/', $phone))
) {
    contactJsonResponse(422, [
        'success' => false,
        'message' => 'Veuillez vérifier les champs du formulaire et réessayer.',
    ]);
}

$autoloadFile = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoloadFile)) {
    error_log('PHPMailer autoloader is missing. Run composer install.');
    contactJsonResponse(500, [
        'success' => false,
        'message' => 'Le service de messagerie est indisponible. Veuillez réessayer plus tard.',
    ]);
}

require_once $autoloadFile;

$localConfigFile = dirname(__DIR__) . '/config/contact-mail.local.php';
$localConfig = is_file($localConfigFile) ? require $localConfigFile : [];
if (!is_array($localConfig)) {
    error_log('ORPAIS contact mail local configuration must return an array.');
    contactJsonResponse(500, [
        'success' => false,
        'message' => 'La configuration du service de messagerie est invalide.',
    ]);
}

$getMailSetting = static function (string $name, string $default = '') use ($localConfig): string {
    $environmentValue = getenv($name);
    if ($environmentValue !== false && $environmentValue !== '') {
        return $environmentValue;
    }

    $localValue = $localConfig[$name] ?? $default;
    return is_string($localValue) ? $localValue : $default;
};

$recipient = trim($getMailSetting('ORPAIS_CONTACT_EMAIL', 'johnngise@gmail.com'));
$smtpHost = trim($getMailSetting('ORPAIS_SMTP_HOST', 'smtp.gmail.com'));
$smtpUsername = trim($getMailSetting('ORPAIS_SMTP_USERNAME', 'johnngise@gmail.com'));
$smtpPassword = trim($getMailSetting('ORPAIS_SMTP_PASSWORD'));
$sender = trim($getMailSetting('ORPAIS_MAIL_FROM', $smtpUsername));
$smtpPortValue = $getMailSetting('ORPAIS_SMTP_PORT', '587');
$smtpPort = filter_var(
    $smtpPortValue,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => 65535]]
);
$smtpEncryption = strtolower(trim($getMailSetting('ORPAIS_SMTP_ENCRYPTION', 'tls')));

if ($smtpPassword === '') {
    error_log('ORPAIS Gmail app password is missing from the contact mail configuration.');
    contactJsonResponse(503, [
        'success' => false,
        'message' => 'L’envoi n’est pas activé : ajoutez le mot de passe d’application Gmail dans config/contact-mail.local.php, puis réessayez.',
    ]);
}

if (
    $smtpHost === ''
    || $smtpUsername === ''
    || $smtpPort === false
    || !in_array($smtpEncryption, ['tls', 'ssl'], true)
    || !filter_var($recipient, FILTER_VALIDATE_EMAIL)
    || !filter_var($sender, FILTER_VALIDATE_EMAIL)
) {
    error_log('ORPAIS contact SMTP configuration is missing or invalid.');
    contactJsonResponse(503, [
        'success' => false,
        'message' => 'Le serveur de messagerie n’est pas encore configuré. Veuillez réessayer plus tard.',
    ]);
}

$safeSubject = preg_replace('/[\r\n]+/', ' ', $subject);
$body = implode("\n", [
    'Nouveau message envoyé depuis le formulaire de contact ORPAIS.',
    '',
    'Nom : ' . $name,
    'E-mail : ' . $email,
    'Téléphone : ' . ($phone !== '' ? $phone : 'Non renseigné'),
    'Sujet : ' . $safeSubject,
    '',
    'Message :',
    $message,
]);

try {
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = $smtpHost;
    $mailer->SMTPAuth = true;
    $mailer->Username = $smtpUsername;
    $mailer->Password = $smtpPassword;
    $mailer->Port = $smtpPort;
    $mailer->SMTPSecure = $smtpEncryption === 'ssl'
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->CharSet = PHPMailer::CHARSET_UTF8;
    $mailer->setFrom($sender, 'ORPAIS');
    $mailer->addAddress($recipient);
    $mailer->addReplyTo($email, $name);
    $mailer->Subject = '[ORPAIS] ' . $safeSubject;
    $mailer->Body = $body;
    $mailer->isHTML(false);
    $mailer->send();
} catch (Exception $exception) {
    error_log('ORPAIS contact email delivery failed: ' . $exception->getMessage());
    contactJsonResponse(503, [
        'success' => false,
        'message' => 'Le message n’a pas pu être envoyé. Vérifiez la configuration du serveur de messagerie ou réessayez plus tard.',
    ]);
}

contactJsonResponse(200, [
    'success' => true,
    'message' => 'Votre message a été envoyé. Merci de nous avoir contactés.',
]);
