<?php
include "../basedados/basedados.h";
include "utilizadores.php";

// Inicia a sessão PHP
session_start();

// Verifica se os dados foram enviados
if (!isset($_POST['utilizador_registo']) || !isset($_POST['password_registo']) || !isset($_POST['confirmar_password'])) {
    die("Os dados não foram enviados corretamente. Por favor, preencha todos os campos.");
}

// Obter os dados do formulário
$user = $_POST["utilizador_registo"];
$pass = $_POST["password_registo"];
$confirmar_pass = $_POST["confirmar_password"];

// md5 para hashing da password
$pass_hashed = md5($pass);

// Por defeito, o tipo de utilizador é CLIENTE_NAO_VALIDO
$tipo = CLIENTE_NAO_VALIDO;

// Validar se as passwords coincidem
if ($pass !== $confirmar_pass) {
    echo "As passwords não coincidem! Redirecionando para o registo...";
    header("refresh:3; url=registo.php");
    exit();
}

// Query para verificar se o utilizador já existe
$stmt_check = $conn->prepare("SELECT nome_utilizador FROM utilizador WHERE nome_utilizador = ?");
$stmt_check->bind_param("s", $user);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "Nome de utilizador já existe. Redirecionando para o registo...";
    header("refresh:3; url=registo.php");
    $stmt_check->close();
    $conn->close();
    exit();
}
$stmt_check->close();

// Obter o último id da carteira (max + 1)
$res = $conn->query("SELECT MAX(id_carteira) AS max_id FROM carteira");
if (!$res) {
    echo "Erro ao obter o último ID da carteira: " . $conn->error;
    $conn->close();
    exit();
}
$row = $res->fetch_assoc();
$new_id_carteira = $row['max_id'] + 1;

// Criar nova carteira
$conn->begin_transaction();

$stmt_carteira = $conn->prepare("INSERT INTO carteira (id_carteira, saldo) VALUES (?, 0)");
if ($stmt_carteira === false) {
    echo "Erro na preparação da query da carteira: " . $conn->error;
    $conn->rollback();
    $conn->close();
    exit();
}
$stmt_carteira->bind_param("i", $new_id_carteira);
if (!$stmt_carteira->execute()) {
    echo "Erro ao criar carteira: " . $stmt_carteira->error;
    $stmt_carteira->close();
    $conn->rollback();
    $conn->close();
    exit();
}
$stmt_carteira->close();

// Inserir o novo utilizador com os dados mínimos
$sql_insert_user = "INSERT INTO utilizador (password, nome_utilizador, tipo_utilizador, id_carteira) VALUES (?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert_user);
if ($stmt_insert === false) {
    echo "Erro na preparação da query do utilizador: " . $conn->error;
    $conn->rollback();
    $conn->close();
    exit();
}
$stmt_insert->bind_param("ssii", $pass_hashed, $user, $tipo, $new_id_carteira);

if ($stmt_insert->execute()) {
    $conn->commit();
    echo "Registo efetuado com sucesso! Redirecionando...";
    header("refresh:3; url=index.php");
} else {
    $conn->rollback();
    echo "Erro ao registar utilizador: " . $stmt_insert->error;
    header("refresh:5; url=registo.php");
}
$stmt_insert->close();
$conn->close();
?>