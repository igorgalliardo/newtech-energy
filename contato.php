<?php

require_once __DIR__ . '/mailer.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

function redirectWithStatus($status) {
    header("Location: index.html?form_status=" . urlencode($status) . "#contato");
    exit;
}

if (!requestComesFromSameHost() || !rateLimitForm('contato')) {
    redirectWithStatus("error");
}

if (!empty($_POST["website"])) {
    header("Location: index.html");
    exit;
}

$formStartedAt = isset($_POST["form_started_at"]) ? (int) $_POST["form_started_at"] : 0;
$now = round(microtime(true) * 1000);
$elapsedSeconds = ($now - $formStartedAt) / 1000;

if ($formStartedAt <= 0 || $elapsedSeconds < 3) {
    header("Location: index.html");
    exit;
}

$nome = cleanText($_POST['nome'] ?? "", 120);
$email = cleanEmail($_POST['email'] ?? "");
$mensagem = cleanText($_POST['mensagem'] ?? "", 2000);

if ($nome === "" || $mensagem === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithStatus("invalid");
}

$assunto = "Novo contato pelo site - NewTech Energy";

$corpo = "Nome: $nome\n";
$corpo .= "Email: $email\n\n";
$corpo .= "Mensagem:\n$mensagem";

if (sendSiteEmail($assunto, $corpo, $email, $nome)) {
    redirectWithStatus("success");
} else {
    redirectWithStatus("error");
}

?>
