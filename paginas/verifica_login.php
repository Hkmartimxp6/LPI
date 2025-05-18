<?php

include "../basedados/basedados.h";

    session_start();

if (!isset($_POST['utilizador']) || !isset($_POST['password'])) {
    die("Os dados não foram enviados corretamente.");
}


	$user = $_POST["utilizador"];
	$pass = $_POST["password"];
	
	
	$sql = "SELECT * FROM utilizador    
		   WHERE nome_utilizador = '$user' AND password ='$pass'";

	$result = mysqli_query ($conn, $sql);
	
	$num = mysqli_num_rows($result);
	
	if($num == 1){
		$_SESSION["utilizador"] = $user;
		
		
		header("refresh:2; url = index.php");	
	}else{
		echo "Utilizador não reconhecido, vai ser redirecionado para a página de login...";
		header("refresh:2; url = login.php");
	}
?>