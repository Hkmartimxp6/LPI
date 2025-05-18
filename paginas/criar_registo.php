<?php
include "../basedados/basedados.h";

session_start();

if (!isset($_POST['utilizador_registo']) || !isset($_POST['password_registo'])) {
    die("Os dados não foram enviados corretamente.");
}

$user = $_POST["utilizador_registo"];
$pass = $_POST["password_registo"];


// Verificar se o utilizador já existe
$sql_check = "SELECT * FROM utilizador WHERE nome_utilizador = '$user'";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    echo "Nome de utilizador já existe. Redirecionando para o registo...";
    header("refresh:2; url=registo.php");
    exit();
}

// Inserir o novo utilizador
$sql = "INSERT INTO utilizador (nome_utilizador, password) VALUES ('$user', '$pass')";
if (mysqli_query($conn, $sql)) {
    $_SESSION["utilizador"] = $user;
    echo "Registo efetuado com sucesso! Redirecionando...";
    header("refresh:2; url=index.php");
} else {
    echo "Erro ao registar: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
