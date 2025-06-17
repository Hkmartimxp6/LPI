<?php
include "../basedados/basedados.h";
include "utilizadores.php";

// Inicializa a sessão PHP
session_start();

// Verifica se a sessão está ativa e válida
if (!isset($_POST['utilizador']) || !isset($_POST['password'])) {
    die("Os dados não foram enviados corretamente.");
}

// Associaçao de variáveis aos dados do formulário
$user = $_POST["utilizador"];
$pass = md5($_POST["password"]);
$nao_valido = CLIENTE_NAO_VALIDO;

// Prepara a consulta para evitar SQL Injection
$stmt = $conn->prepare("SELECT * FROM utilizador WHERE nome_utilizador = ? AND password = ?");
// Faz o bind do parâmetro
$stmt->bind_param("ss", $user, $pass);
// Executa a consulta e obtém o resultado
$stmt->execute();
// Obtém o resultado da consulta
$result = $stmt->get_result();

// Verifica se o utilizador existe e se a password está correta
if ($result->num_rows === 1) {
    // Busca os dados do utilizador
    $user_info = $result->fetch_assoc();

    // Verifica se o tipo de utilizador é válido
    if ($user_info['tipo_utilizador'] != $nao_valido) {
        $_SESSION["utilizador"] = $user_info;
        echo "Login efetuado com sucesso, vai ser redirecionado para a página inicial...";
        header("refresh:2; url=index.php");
        exit();
    } else {
        echo "Utilizador não validado, vai ser redirecionado para a página inicial...";
        header("refresh:2; url=index.php");
        exit();
    }
} else {
    echo "Utilizador ou password incorretos, ou utilizador não existe.";
    header("refresh:4; url=login.php");
}

// Fecha a declaração
$stmt->close();
// Fecha a conexão com a base de dados
$conn->close();
?>