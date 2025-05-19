<?php
include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

if (!isset($_POST['utilizador_registo']) || !isset($_POST['password_registo'])) {
    die("Os dados não foram enviados corretamente.");
}

$user = $_POST["utilizador_registo"];
$pass = md5($_POST["password_registo"]);
$tipo = CLIENTE_NAO_VALIDO;

// Querry para verificar se o utilizador já existe
$stmt_check = $conn->prepare("SELECT * FROM utilizador WHERE nome_utilizador = ?");
$stmt_check->bind_param("s", $user);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// Verificar se o utilizador já existe na bd
if ($result_check->num_rows > 0) {
    echo "Nome de utilizador já existe. Redirecionando para o registo...";
    header("refresh:2; url=registo.php");
    exit();
}

// Obter o ultimo id da carteira (max + 1)
$res = $conn->query("SELECT MAX(id_carteira) AS max_id FROM carteira");
$row = $res->fetch_assoc();
$new_id_carteira = $row['max_id'] + 1;

// Criar nova carteira
$stmt_carteira = $conn->prepare("INSERT INTO carteira (id_carteira, saldo) VALUES (?, 0)");
$stmt_carteira->bind_param("i", $new_id_carteira);
if (!$stmt_carteira->execute()) {
    echo "Erro ao criar carteira: " . $stmt_carteira->error;
    exit();
}
$stmt_carteira->close();

// Inserir o novo utilizador
$stmt_insert = $conn->prepare("INSERT INTO utilizador (nome_utilizador, password, tipo_utilizador, id_carteira) VALUES (?, ?, ?, ?)");
$stmt_insert->bind_param("ssii", $user, $pass, $tipo, $new_id_carteira);

// caso o insert funcione, voltar para a pagina inicial
if ($stmt_insert->execute()) {
    echo "Registo efetuado com sucesso! Redirecionando...";
    header("refresh:2; url=index.php");
// caso não funcione, dar erro
} else {
    echo "Erro ao registar utilizador: " . $stmt_insert->error;
    header("refresh:2; url=registo.php");
}

$stmt_check->close();
$stmt_insert->close();
$conn->close();
?>