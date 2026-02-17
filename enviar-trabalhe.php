<?php
$nome = $_POST['nome'];
$email = $_POST['email'];

$arquivo = $_FILES['curriculo']['tmp_name'];
$nomeArquivo = $_FILES['curriculo']['name'];

$destino = "uploads/" . $nomeArquivo;
move_uploaded_file($arquivo, $destino);

$to = "contato@newtechenergy.com.br";
$subject = "Novo Currículo - $nome";

$message = "Nome: $nome\nEmail: $email\nArquivo anexado: $nomeArquivo";

mail($to, $subject, $message);

echo "Enviado com sucesso!";
?>