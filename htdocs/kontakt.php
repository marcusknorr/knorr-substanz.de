<?php
declare(strict_types=1);
// PHP 8.2+; IONOS SMTP with STARTTLS. Never use PHP mail().
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
function result(int $status, array $data): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST'); result(405, ['error' => 'Diese Anfrage wird nicht unterstützt.']);
}
$https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '';
if (!$https) result(403, ['error' => 'Bitte öffnen Sie die Website über HTTPS.']);
define('KNORR_CONTACT', true);
$config = require __DIR__ . '/private/config.php';
if (!$config['enabled'] || $config['smtp_password'] === '') {
    result(503, ['error' => 'Der Direktversand ist derzeit nicht verfügbar. Bitte kontaktieren Sie uns per E-Mail oder Telefon.']);
}
$origins = ['https://www.knorr-substanz.de', 'https://knorr-substanz.de'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && !in_array($origin, $origins, true)) result(403, ['error' => 'Bitte verwenden Sie das Formular auf unserer Website.']);
if (($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'cross-site') result(403, ['error' => 'Ungültiger Zugriff.']);
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 32768) result(413, ['error' => 'Die Nachricht ist zu lang.']);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', '1800');
session_name('knorr_contact');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
if (!session_start()) result(503, ['error' => 'Der Versand ist gerade nicht verfügbar. Bitte schreiben Sie uns eine E-Mail.']);
$now = time();
if ($method === 'GET') {
    if (!isset($_SESSION['csrf'], $_SESSION['created']) || $now - $_SESSION['created'] > 1800) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        $_SESSION['created'] = $now;
    }
    result(200, ['ready' => true, 'csrf' => $_SESSION['csrf']]);
}
if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/x-www-form-urlencoded') !== 0) result(415, ['error' => 'Ungültiges Nachrichtenformat.']);
function field(string $name): string {
    if (!isset($_POST[$name]) || !is_string($_POST[$name])) return '';
    return trim($_POST[$name]);
}
$csrf = field('csrf');
if (!isset($_SESSION['csrf'], $_SESSION['created']) || $now - $_SESSION['created'] > 1800 || !hash_equals($_SESSION['csrf'], $csrf)) {
    result(403, ['error' => 'Die Formularsitzung ist abgelaufen. Bitte laden Sie die Seite neu.']);
}
if (field('website') !== '') result(422, ['error' => 'Die Anfrage konnte nicht angenommen werden. Bitte kontaktieren Sie uns per E-Mail.']);
$name = field('name'); $email = field('email'); $phone = field('phone'); $message = field('message');
$errors = [];
// UTF-8 character counts; reject malformed encoding as well as oversized input.
function chars(string $value): int { $n = preg_match_all('/./us', $value); return $n === false ? PHP_INT_MAX : $n; }
if ($name === '' || chars($name) > 120 || preg_match('/[\x00-\x1F\x7F]/', $name)) $errors['name'] = 'Bitte geben Sie Ihren Namen ein (maximal 120 Zeichen).';
if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) $errors['email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
if (chars($phone) > 40 || preg_match('/[\x00-\x1F\x7F]/', $phone)) $errors['phone'] = 'Bitte prüfen Sie die Telefonnummer.';
if (chars($message) < 10 || chars($message) > 5000 || strpos($message, "\0") !== false) $errors['message'] = 'Bitte schreiben Sie zwischen 10 und 5.000 Zeichen.';
if ($errors) result(422, ['error' => implode(' ', $errors), 'fields' => array_keys($errors)]);
// One locked file: per-IP and total limits across all sessions, no message storage.
// PHP prefix prevents disclosure even if the web server ignores .htaccess.
$rateFile = __DIR__ . '/private/rate.php';
$fp = @fopen($rateFile, 'c+');
if (!$fp || !flock($fp, LOCK_EX)) result(503, ['error' => 'Der Versand ist gerade nicht verfügbar. Bitte schreiben Sie uns eine E-Mail.']);
$prefix = "<?php exit; ?>\n";
$raw = stream_get_contents($fp);
$entries = $raw === '' ? [] : json_decode(substr($raw, strlen($prefix)), true);
if (!is_array($entries)) { flock($fp, LOCK_UN); fclose($fp); result(503, ['error' => 'Der Versand ist gerade nicht verfügbar.']); }
$entries = array_values(array_filter($entries, static function ($entry) use ($now) { return is_array($entry) && isset($entry['time'], $entry['key']) && (int)$entry['time'] > $now - 3600; }));
$key = hash_hmac('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . gmdate('Y-m-d'), $config['rate_key']);
$recent = count(array_filter($entries, static function ($entry) use ($key, $now) { return $entry['key'] === $key && (int)$entry['time'] > $now - 900; }));
$limited = $recent >= 5 || count($entries) >= 30;
if (!$limited) $entries[] = ['time' => $now, 'key' => $key];
$data = $prefix . json_encode($entries);
rewind($fp); $written = fwrite($fp, $data); ftruncate($fp, strlen($data)); fflush($fp); flock($fp, LOCK_UN); fclose($fp);
if ($written !== strlen($data)) result(503, ['error' => 'Der Versand ist gerade nicht verfügbar.']);
if ($limited) { header('Retry-After: 900'); result(429, ['error' => 'Es wurden zu viele Anfragen gesendet. Bitte versuchen Sie es später oder rufen Sie uns an.']); }
require __DIR__ . '/private/phpmailer/Exception.php';
require __DIR__ . '/private/phpmailer/PHPMailer.php';
require __DIR__ . '/private/phpmailer/SMTP.php';
try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->Port = (int)$config['smtp_port'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_password'];
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPDebug = 0;
    $mail->Timeout = 15;
    $mail->Timelimit = 20;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('contact@marcus-knorr-design.de', 'Marcus Knorr Website');
    $mail->addAddress('contact@marcus-knorr-design.de', 'Marcus Knorr');
    $mail->addReplyTo($email, $name);
    $mail->isHTML(false);
    $mail->Subject = 'Kontaktanfrage über knorr-substanz.de';
    $mail->Body = "Name: $name\nE-Mail: $email\nTelefon: " . ($phone ?: 'nicht angegeben') . "\n\nAnliegen:\n$message\n";
    $mail->send();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $_SESSION['created'] = time();
    result(200, ['sent' => true, 'csrf' => $_SESSION['csrf'], 'message' => 'Vielen Dank. Ihre Nachricht wurde zum Versand angenommen.']);
} catch (Throwable $error) {
    // Do not expose SMTP server details, credentials or message content.
    result(502, ['error' => 'Die Nachricht konnte nicht versendet werden. Ihre Eingaben bleiben erhalten. Bitte versuchen Sie es später oder schreiben Sie uns direkt eine E-Mail.']);
}
