<?php

require_once __DIR__ . '/mailer.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

function redirectWithStatus($status) {
    header("Location: seja-um-franqueado.html?form_status=" . urlencode($status));
    exit;
}

if (!requestComesFromSameHost() || !rateLimitForm('franqueado')) {
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
$telefone = cleanText($_POST['telefone'] ?? "", 30);
$cidade = cleanText($_POST['cidade'] ?? "", 120);
$investimento = cleanText($_POST['investimento'] ?? "", 80);
$mensagem = cleanText($_POST['mensagem'] ?? "", 2000);

if ($nome === "" || $telefone === "" || $cidade === "" || $investimento === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithStatus("invalid");
}

$assunto = "Novo Interesse em Franquia - NewTech Energy";

$corpo = "
Nome: $nome
Email: $email
Telefone: $telefone
Cidade: $cidade
Capital Disponível: $investimento

Mensagem:
$mensagem
";

if (sendSiteEmail($assunto, $corpo, $email, $nome)) {
    redirectWithStatus("success");
}

redirectWithStatus("error");

?>
