<?php
session_start();

if (isset($_SESSION["utilizador"])) {

    $user = $_SESSION["utilizador"];
    unset($_SESSION);
    $_SESSION["utilizador"] = $user;

    include "../basedados/basedados.h";
    include "utilizadores.php";

    $sql = "SELECT * FROM utilizador WHERE nome_utilizador = '" . $_SESSION["utilizador"] . "'";
    $retval = mysqli_query($conn, $sql);

    if (!$retval) {
        die('Erro ao obter dados: ' . mysqli_error($conn));
    }

    $row = mysqli_fetch_array($retval);

    if ($row["tipo_utilizador"] != 4 && $row["tipo_utilizador"] != 5) {

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

        switch ($row["tipo_utilizador"]) {
            case ADMINISTRADOR: // admin
                echo "<div id='corpo'>";
                printDadosPessoais();
                printGestaoBilhetes();
                printGestaoUtilizadores();
                printGestaoCarteira();
                printGestaoRotas();
                echo "</div>";
                break;

            case FUNCIONARIO: // funcionario
                echo "<div id='corpo'>";
                printDadosPessoais();
                printGestaoCarteira();
                printGestaoBilhetes();
                echo "</div>";
                break;

            case CLIENTE: // cliente
                echo "<div id='corpo'>";
                printGestaoBilhetes();
                printGestaoCarteira();
                printDadosPessoais();
                echo "</div>";
                break;
        }

        echo "
            <div id='botao'>
                <form action='logout.php'><input type='submit' value='Logout'></form>
            </div>";
    } else {
        echo "<script>setTimeout(function(){ window.location.href = 'logout.php'; }, 0)</script>";
    }
} else {
    echo "<script>setTimeout(function(){ window.location.href = 'logout.php'; }, 0)</script>";
}

function printGestaoBilhetes()
{
    echo "<div class='botaoCorpo'>
            <form action='bilhetes.php'>
                <input type='submit' value='Gestão Bilhetes' id='btCorpo'>
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

function printGestaoCarteira()
{
    echo "<div class='botaoCorpo'>
            <form action='carteira.php'>
                <input type='submit' value='Gestão Carteira' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoRotas()
{
    echo "<div class='botaoCorpo'>
            <form action='pagina_gestap_rotas.php'>
                <input type='submit' value='Gestão Rotas' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoUtilizadores()
{
    echo "<div class='botaoCorpo'>
            <form action='pagina_gestao_utilizadores.php'>
                <input type='submit' value='Gestão Utilizadores' id='btCorpo'>
            </form>
          </div>";
}
