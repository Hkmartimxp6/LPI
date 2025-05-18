<?php
include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

if (!isset($_POST['utilizador_registo']) || !isset($_POST['password_registo'])) {
    die("Os dados não foram enviados corretamente.");
}

$user = $_POST["utilizador_registo"];
$pass = md5($_POST["password_registo"]);  // MD5 da password
$tipo = CLIENTE_NAO_VALIDO;

// Verificar se o utilizador já existe
$stmt_check = $conn->prepare("SELECT * FROM utilizador WHERE nome_utilizador = ?");
$stmt_check->bind_param("s", $user);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "Nome de utilizador já existe. Redirecionando para o registo...";
    header("refresh:2; url=registo.php");
    exit();
}

// Inserir o novo utilizador
$stmt_insert = $conn->prepare("INSERT INTO utilizador (nome_utilizador, password, tipo_utilizador) VALUES (?, ?, ?)");
$stmt_insert->bind_param("ssi", $user, $pass, $tipo);

if ($stmt_insert->execute()) {
    $_SESSION["utilizador"] = $user;
    echo "Registo efetuado com sucesso! Redirecionando...";
    header("refresh:2; url=index.php");
} else {
    echo "Erro ao registar: " . $stmt_insert->error;
}

$stmt_check->close();
$stmt_insert->close();
$conn->close();
?>
