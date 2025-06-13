<?php
include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

if (!isset($_POST['utilizador']) || !isset($_POST['password'])) {
    die("Os dados não foram enviados corretamente.");
}

$user = $_POST["utilizador"];
$pass = md5($_POST["password"]);
$nao_valido = CLIENTE_NAO_VALIDO;

$stmt = $conn->prepare("SELECT * FROM utilizador WHERE nome_utilizador = ? AND password = ?");
$stmt->bind_param("ss", $user, $pass);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user_info = $result->fetch_assoc();

    if ($user_info['tipo_utilizador'] != $nao_valido) {
        $_SESSION["utilizador"] = $user_info; // ✅ guarda o array completo
        echo "Login efetuado com sucesso, vai ser redirecionado para a página inicial...";
        header("refresh:2; url=index.php");
        exit();
    } else {
        echo "Utilizador não validado, vai ser redirecionado para a página de login...";
        header("refresh:2; url=login.php");
        exit();
    }
} else {
    echo "Utilizador ou password incorretos, ou utilizador não existe.";
    header("refresh:4; url=login.php");
}

$stmt->close();
$conn->close();
?>
