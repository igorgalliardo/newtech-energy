<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

function loadMailConfig() {
    $config = require __DIR__ . '/mail-config.php';
    $localConfigPath = __DIR__ . '/mail-config.local.php';

    if (is_file($localConfigPath)) {
        $localConfig = require $localConfigPath;
        $config = array_merge($config, $localConfig);
    }

    return $config;
}

function sendSiteEmail($subject, $body, $replyToEmail = null, $replyToName = null, array $attachments = []) {
    $config = loadMailConfig();

    if (empty($config['host']) || empty($config['username']) || empty($config['password'])) {
        error_log('SMTP config incomplete.');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->Port = (int) $config['port'];

        if ($config['secure'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($config['secure'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($config['to_email'], $config['to_name']);

        if ($replyToEmail && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyToEmail, $replyToName ?: $replyToEmail);
        }

        foreach ($attachments as $attachment) {
            $mail->addAttachment(
                $attachment['path'],
                $attachment['name'],
                PHPMailer::ENCODING_BASE64,
                $attachment['mime']
            );
        }

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML(false);

        return $mail->send();
    } catch (Exception $exception) {
        error_log('SMTP send failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function cleanText($value, $maxLength = 1000) {
    $value = trim(strip_tags((string) $value));
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return substr($value, 0, $maxLength);
}

function cleanEmail($value) {
    return str_replace(["\r", "\n"], '', trim((string) $value));
}

function requestComesFromSameHost() {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    if ($host === '') {
        return true;
    }

    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $header) {
        if (empty($_SERVER[$header])) {
            continue;
        }

        $requestHost = parse_url($_SERVER[$header], PHP_URL_HOST);

        if ($requestHost && strcasecmp($requestHost, preg_replace('/:\d+$/', '', $host)) !== 0) {
            return false;
        }
    }

    return true;
}

function rateLimitForm($formName, $maxAttempts = 5, $windowSeconds = 600) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = hash('sha256', $formName . '|' . $ip);
    $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nte_form_rate_' . $key . '.json';
    $now = time();
    $attempts = [];

    if (is_file($file)) {
        $attempts = json_decode((string) file_get_contents($file), true);
        $attempts = is_array($attempts) ? $attempts : [];
    }

    $attempts = array_values(array_filter($attempts, function ($timestamp) use ($now, $windowSeconds) {
        return is_int($timestamp) && ($now - $timestamp) < $windowSeconds;
    }));

    if (count($attempts) >= $maxAttempts) {
        return false;
    }

    $attempts[] = $now;
    file_put_contents($file, json_encode($attempts), LOCK_EX);

    return true;
}
