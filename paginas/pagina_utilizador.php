<?php
include "../basedados/basedados.h";
include "utilizadores.php";

session_start();

// Verifica se a sessão está ativa e válida
if (!isset($_SESSION["utilizador"]) || !isset($_SESSION["utilizador"]["nome_utilizador"])) {
    header("Location: logout.php");
    exit();
}

// Obter o nome de utilizador da sessão
$nome_utilizador = $_SESSION["utilizador"]["nome_utilizador"];

// Consulta os dados atualizados do utilizador (caso tenham mudado na base de dados)
$sql = "SELECT * FROM utilizador WHERE nome_utilizador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nome_utilizador);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
    header("Location: logout.php");
    exit();
}

$row = $result->fetch_assoc();

// Verifica se o utilizador está num estado inválido
if ($row["tipo_utilizador"] == 4 || $row["tipo_utilizador"] == 5) {
    header("Location: logout.php");
    exit();
}

// Começo do HTML
echo "<div id='cabecalho'>
    <div class='logo'>
        <a href='index.php'><img src='logo.png' style='width:300px;' alt='#'></a>
    </div>
    <div class='input-div'>
        <div id='botao'>
            <form action='index.php'><input type='submit' value='Página Principal'></form>
        </div>
    </div>
</div>";

// Conteúdo baseado no tipo de utilizador
echo "<div id='corpo'>";
switch ($row["tipo_utilizador"]) {
    case ADMINISTRADOR: // 1
        printGestaoAlertas();
        printDadosPessoais();
        printGestaoUtilizadores();
        printGestaoRotas();
        printGestaoCarteiras();
        break;

    case FUNCIONARIO: // 2
        printDadosPessoais();
        printGestaoCarteiraPessoal();
        printGestaoBilhetes();
        printCompraBilhetesParaCliente();
        printGestaoCarteiras();
        break;

    case CLIENTE: // 3
        printGestaoBilhetes();
        printGestaoCarteiraPessoal();
        printDadosPessoais();
        break;
}
echo "</div>";

// Botão logout
echo "
    <div id='botao'>
        <form action='logout.php'><input type='submit' value='Logout'></form>
    </div>";

// === Funções auxiliares ===

function printGestaoBilhetes()
{
    echo "<div class='botaoCorpo'>
            <form action='gestao_bilhetes.php'>
                <input type='submit' value='Gestão Bilhetes' id='btCorpo'>
            </form>
          </div>";
}

function printCompraBilhetesParaCliente()
{
    echo "<div class='botaoCorpo'>
            <form action='comprar_bilhetes_funcionario.php'>
                <input type='submit' value='Compra Bilhetes para Clientes' id='btCorpo'>
            </form>
          </div>";
}


function printDadosPessoais()
{
    echo "<div class='botaoCorpo'>
            <form action='pagina_dados_pessoais.php'>
                <input type='submit' value='Dados Pessoais' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoCarteiraPessoal()
{
    echo "<div class='botaoCorpo'>
            <form action='carteira.php'>
                <input type='submit' value='Gestão Carteira Pessoal' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoCarteiras()
{
    echo "<div class='botaoCorpo'>
            <form action='gestao_carteiras.php'>
                <input type='submit' value='Gestão de Carteiras dos Clientes' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoRotas()
{
    echo "<div class='botaoCorpo'>
            <form action='gestao_rotas.php'>
                <input type='submit' value='Gestão Rotas' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoUtilizadores()
{
    echo "<div class='botaoCorpo'>
            <form action='gestao_utilizadores.php'>
                <input type='submit' value='Gestão Utilizadores' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoAlertas()
{
    echo "<div class='botaoCorpo'>
            <form action='gestao_alertas.php'>
                <input type='submit' value='Gestão Alertas' id='btCorpo'>
            </form>
          </div>";
}
