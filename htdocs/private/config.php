<?php
// Nur auf dem Webspace bearbeiten. Passwort niemals im Chat teilen.
if (!defined('KNORR_CONTACT')) { http_response_code(404); exit; }
return [
    'enabled' => false, // Nach Einrichtung und Hosting-Klärung auf true setzen.
    'smtp_host' => 'smtp.ionos.de',
    'smtp_port' => 587,
    'smtp_user' => 'contact@marcus-knorr-design.de',
    'smtp_password' => '', // Passwort dieses E-Mail-Postfachs, nicht das Kundenkonto-Passwort.
    'rate_key' => '025c1cab5acce1aee3919bb95f1d9e94bcad28fd1e8afd15cff40261b30e756b',
];
