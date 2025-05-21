<html>
<body>  
	
	<?php	
		session_start();
		
		if(isset($_SESSION["utilizador"])){
			
				
			$user = $_SESSION["utilizador"];
			unset($_SESSION);
			$_SESSION["utilizador"] = $user;
						
			// ===============================================================
			include '../base_dados/basedados.h';
			include "utilizadores.php";
			//Selecionar user correspondente da base de dados
			$sql = "SELECT * FROM utilizador WHERE nome_utilizador = '".$_SESSION["utilizador"]."'";
			$retval = mysqli_query( $conn, $sql );
			if(! $retval ){
				die('Could not get data: ' . mysqli_error($conn));// se não funcionar dá erro
			}
			$row = mysqli_fetch_array($retval);
			
			if($row["tipoUtilizador"]!=CLIENTE_NAO_VALIDO && $row["tipoUtilizador"]!=CLIENTE_APAGADO){
				// ===============================================================
				
				echo"<div id='cabecalho'>
						<a href='../pagina_inicial.php'>
							<div id='logo'>
							</div>
						</a>
							<img src = '../../media/imgs_utilizadores/".$row['imagem']."' width=100 height = 100 id=img>
						<div class= 'input-div'>
							<div id='botao'>
								<form action='logout.php'>
									<input type='submit' value='Logout'>
								</form>
							</div>
							<div id='botao'>
								<form action='../pagina_inicial.php'>
									<input type='submit' value='Página Principal'>
								</form>
							</div>
							<div id='botao'>
							  <form action='../contatos.php'>
								<input type='submit' value='Contactos'>
							  </form>
							</div>
						</div>
					</div>";
				
				//PERSONALIZAÇÃO
				switch($row["tipoUtilizador"]){
						
					case ADMINISTRADOR:
						//==============================ADMINISTRADOR===============================//
						echo "<div id='corpo'>";
						printDadosPessoais();
						printGestãoReservas();
						printGestãoUtilizadores();
						printGestãoCabanas();
						echo"</div>";
					break;
					
					case FUNCIONARIO:
						//===============================FUNCIONARIO================================//
						echo "<div id='corpo'>";
						printDadosPessoais();
						printGestãoReservas();
						echo"</div>";
					break;
						
					case CLIENTE:
						//=================================CLIENTE==================================//
						echo "<div id='corpo'>";
						printContactos();
						printGestãoReservas();
						printDadosPessoais();
						echo"</div>";
					break;
					
				}
				
			}else{
				echo "<script>setTimeout(function(){ window.location.href = 'logout.php'; }, 0)</script>";
			}
			
		}else
			echo "<script>setTimeout(function(){ window.location.href = 'logout.php'; }, 0)</script>";
			
		function printContactos(){
			//Contactos
			echo 
			"<div class='botaoCorpo'>
				<form action='../contatos.php'>
					<input type='submit' value='Contactos' id='btCorpo'>
				</form>
			</div>";
			
		}
		
		function printGestãoCabanas(){
			//Contactos
			echo 
			"<div class='botaoCorpo'>
				<form action='../Cabanas/PgGestCabanas.php'>
					<input type='submit' value='Gestão Cabanas' id='btCorpo'>
				</form>
			</div>";
			
		}
		
		function printDadosPessoais(){
			//Dados Pessoais
			echo
			"<div class='botaoCorpo'>
				<form action= 'DadosPessoais.php'>
					<input type='submit' value='Dados Pessoais' id='btCorpo'>
				</form>
			</div>";
		}

		function printGestãoQuotas(){
			//Quotas
			echo 
			"<div class='botaoCorpo'>
				<form action='PgQuotas.php'>
					<input type='submit' value='Gestão Quotas' id='btCorpo'>
				</form>
			</div>";
		}
		
		function printGestãoReservas(){
			//Gestão Reservas
			echo
			"<div class='botaoCorpo'>
				<form action='../Reserva/PgGestReservas.php'>
					<input type='submit' value='Gestão Reservas' id='btCorpo'>
				</form>
			</div>";
		}
		
		function printGestãoUtilizadores(){
			//Gestão Utilizadores
			echo 
			"<div class='botaoCorpo'>
				<form action='PgGestUtilizadores.php'>
					<input type='submit' value='Gestão Utilizadores' id='btCorpo'>
				</form>
			</div>";
		}
	?>
</body>