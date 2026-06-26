<?php

require_once __DIR__ . '/mailer.php';

$maxSize = 5 * 1024 * 1024;

function redirectWithStatus($status) {
    header("Location: trabalhe-conosco.html?form_status=" . urlencode($status));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

if (!requestComesFromSameHost() || !rateLimitForm('trabalhe')) {
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
$cargo = cleanText($_POST['cargo'] ?? "", 120);
$mensagem = cleanText($_POST['mensagem'] ?? "", 2000);

if ($nome === "" || $telefone === "" || $cargo === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithStatus("invalid");
}

if (!isset($_FILES['curriculo']) || $_FILES['curriculo']['error'] !== 0) {
    redirectWithStatus("file_error");
}

$arquivo = $_FILES['curriculo'];

if ($arquivo['size'] > $maxSize) {
    redirectWithStatus("file_size");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $arquivo['tmp_name']);
finfo_close($finfo);

$tiposPermitidos = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

if (!in_array($mime, $tiposPermitidos)) {
    redirectWithStatus("file_type");
}

$nomeArquivo = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", basename($arquivo['name']));

$conteudo = "
Novo currículo recebido:

Nome: $nome
Email: $email
Telefone: $telefone
Cargo desejado: $cargo

Mensagem:
$mensagem
";

$attachments = [[
    'path' => $arquivo['tmp_name'],
    'name' => $nomeArquivo,
    'mime' => $mime,
]];

if (sendSiteEmail("Novo Currículo - $nome", $conteudo, $email, $nome, $attachments)) {
    redirectWithStatus("success");
}

redirectWithStatus("error");

?>
