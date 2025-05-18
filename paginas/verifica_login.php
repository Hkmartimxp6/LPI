<?php

include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

if (!isset($_POST['utilizador']) || !isset($_POST['password'])) {
    die("Os dados não foram enviados corretamente.");
}

$user = $_POST["utilizador"];
$pass = $_POST["password"];
$nao_valido = CLIENTE_NAO_VALIDO;

$sql = "SELECT * FROM utilizador    
        WHERE nome_utilizador = '$user' AND password ='$pass'";


$result = mysqli_query($conn, $sql);
$user_info = mysqli_fetch_assoc($result);

$num = mysqli_num_rows($result);

if($user_info['tipo_utilizador'] != $nao_valido){

if ($num == 1) {
    $_SESSION["utilizador"] = $user;
    echo "Login efetuado com sucesso, vai ser redirecionado para a página inicial... ";
    header("refresh:2; url = index.php");
} else {
    echo "Utilizador não reconhecido, vai ser redirecionado para a página de login...";
    header("refresh:2; url = login.php");
}
}else{
    echo "Utilizador não validado, vai ser redirecionado para a página de login...";
    header("refresh:2; url = login.php");
}
?>

