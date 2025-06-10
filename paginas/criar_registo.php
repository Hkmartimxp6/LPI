<?php
include "../basedados/basedados.h";
include "utilizadores.php"; // Certifique-se de que este ficheiro define CLIENTE_NAO_VALIDO se for uma constante

session_start();

// Verifica se os dados mínimos foram enviados corretamente
if (!isset($_POST['utilizador_registo']) || !isset($_POST['password_registo']) || !isset($_POST['email_registo']) ||
    !isset($_POST['nome']) || !isset($_POST['morada']) || !isset($_POST['telemovel'])) {
    die("Os dados não foram enviados corretamente. Por favor, preencha todos os campos.");
}

// Obter os dados do formulário
$user = $_POST["utilizador_registo"];
$pass_raw = $_POST["password_registo"];
$confirmar_pass = $_POST["confirmar_password"];
$email = $_POST["email_registo"];
$nome = $_POST["nome"];
$morada = $_POST["morada"];
$telemovel = $_POST["telemovel"];

// Usando md5 para hashing da password como no seu código original
$pass_hashed = md5($pass_raw);
$tipo = CLIENTE_NAO_VALIDO; // Assumindo que CLIENTE_NAO_VALIDO está definido em utilizadores.php

// 1. Validar se as passwords coincidem
if ($pass_raw !== $confirmar_pass) {
    echo "As passwords não coincidem! Redirecionando para o registo...";
    header("refresh:3; url=registo.php");
    exit();
}


// 2. Query para verificar se o utilizador ou email já existem
$stmt_check = $conn->prepare("SELECT nome_utilizador FROM utilizador WHERE nome_utilizador = ? OR email = ?");
$stmt_check->bind_param("ss", $user, $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// Verificar se o utilizador ou email já existe na bd
if ($result_check->num_rows > 0) {
    echo "Nome de utilizador ou email já existe. Redirecionando para o registo...";
    header("refresh:3; url=registo.php");
    $stmt_check->close();
    $conn->close();
    exit();
}
$stmt_check->close();


// 3. Obter o ultimo id da carteira (max + 1)
$res = $conn->query("SELECT MAX(id_carteira) AS max_id FROM carteira");
if (!$res) {
    echo "Erro ao obter o último ID da carteira: " . $conn->error;
    $conn->close();
    exit();
}
$row = $res->fetch_assoc();
$new_id_carteira = $row['max_id'] + 1;


// 4. Criar nova carteira
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


// 5. Inserir o novo utilizador com os novos campos
// A coluna na BD é 'nome'.
$sql_insert_user = "INSERT INTO utilizador (password, nome_utilizador, nome, morada, telemovel, tipo_utilizador, id_carteira, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert_user);

if ($stmt_insert === false) {
    echo "Erro na preparação da query do utilizador: " . $conn->error;
    $conn->rollback();
    $conn->close();
    exit();
}

// **CORREÇÃO AQUI:** Ajustei a ordem dos parâmetros no bind_param
// A ordem das variáveis deve corresponder à ordem das colunas na query SQL:
// (password, nome_utilizador, nome, morada, telemovel, tipo_utilizador, id_carteira, email)
// Tipos:   s (password), s (nome_utilizador), s (nome), s (morada), s (telemovel), i (tipo_utilizador), i (id_carteira), s (email)
$stmt_insert->bind_param("sssssiis", $pass_hashed, $user, $nome, $morada, $telemovel, $tipo, $new_id_carteira, $email);


// Caso o insert funcione, comitar a transação e voltar para a pagina inicial
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