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
// Faz o bind da variável $user à query
$stmt_check->bind_param("s", $user);
// Executa a query
$stmt_check->execute();
// Obtém o resultado da query
$result_check = $stmt_check->get_result();

// Verifica se o utilizador já existe
if ($result_check->num_rows > 0) {
    echo "Nome de utilizador já existe. Redirecionando para o registo...";
    header("refresh:3; url=registo.php");
    $stmt_check->close();
    $conn->close();
    exit();
}
// Fecha o statement de verificação
$stmt_check->close();

// Obter o último id da carteira (max + 1)
$res = $conn->query("SELECT MAX(id_carteira) AS max_id FROM carteira");
// Verifica se a query foi bem sucedida
if (!$res) {
    echo "Erro ao obter o último ID da carteira: " . $conn->error;
    $conn->close();
    exit();
}
// Busca o resultado da query
$row = $res->fetch_assoc();
// Verifica se o resultado é válido
$new_id_carteira = $row['max_id'] + 1;

// Criar nova carteira e iniciar a transação
$conn->begin_transaction();

// Preparar a query para inserir a nova carteira
$stmt_carteira = $conn->prepare("INSERT INTO carteira (id_carteira, saldo) VALUES (?, 0)");
// Verifica se a preparação da query não foi bem sucedida
// imprime uma mensagem de erro e reverte a transação
if ($stmt_carteira === false) {
    echo "Erro na preparação da query da carteira: " . $conn->error;
    $conn->rollback();
    $conn->close();
    exit();
}
// Faz o bind do novo id da carteira à query
$stmt_carteira->bind_param("i", $new_id_carteira);

// Se a execução da query falhar, imprime uma mensagem de erro e reverte a transação
if (!$stmt_carteira->execute()) {
    echo "Erro ao criar carteira: " . $stmt_carteira->error;
    $stmt_carteira->close();
    $conn->rollback();
    $conn->close();
    exit();
}
$stmt_carteira->close();

// Inserir o novo utilizador com os dados mínimos
$sql_insert_user = "INSERT INTO utilizador (password, nome_utilizador, tipo_utilizador, id_carteira) 
                    VALUES (?, ?, ?, ?)";

// Preparar a query para inserir o novo utilizador
$stmt_insert = $conn->prepare($sql_insert_user);

// Verifica se a preparação da query não foi bem sucedida
if ($stmt_insert === false) {
    echo "Erro na preparação da query do utilizador: " . $conn->error;
    $conn->rollback();
    $conn->close();
    exit();
}
// Faz o bind das variáveis à query
$stmt_insert->bind_param("ssii", $pass_hashed, $user, $tipo, $new_id_carteira);

// Executa a query para inserir o novo utilizador
if ($stmt_insert->execute()) {
    $conn->commit();
    echo "Registo efetuado com sucesso! Redirecionando...";
    header("refresh:3; url=index.php");
} else {
    $conn->rollback();
    echo "Erro ao registar utilizador: " . $stmt_insert->error;
    header("refresh:5; url=registo.php");
}

// Fecha o statement de inserção e a conexão
$stmt_insert->close();
$conn->close();
?>