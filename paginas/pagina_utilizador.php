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
                <form action='logout.php'><input type='submit' value='Logout'></form>
            </div>
            <div id='botao'>
                <form action='index.php'><input type='submit' value='Página Principal'></form>
            </div>
            <div id='botao'>
                <form action='../contatos.php'><input type='submit' value='Contactos'></form>
            </div>
        </div>
      </div>";

        switch ($row["tipo_utilizador"]) {
            case 1: // admin
                echo "<div id='corpo'>";
                printDadosPessoais();
                printGestãoReservas();
                printGestãoUtilizadores();
                printGestãoCabanas();
                echo "</div>";
                break;

            case 2: // funcionario
                echo "<div id='corpo'>";
                printDadosPessoais();
                printGestãoReservas();
                echo "</div>";
                break;

            case 3: // cliente
                echo "<div id='corpo'>";
                printContactos();
                printGestãoReservas();
                printDadosPessoais();
                echo "</div>";
                break;
        }
    } else {
        echo "<script>setTimeout(function(){ window.location.href = 'logout.php'; }, 0)</script>";
    }
} else {
    echo "<script>setTimeout(function(){ window.location.href = 'logout.php'; }, 0)</script>";
}

function printContactos()
{
    echo "<div class='botaoCorpo'>
            <form action='../contatos.php'>
                <input type='submit' value='Contactos' id='btCorpo'>
            </form>
          </div>";
}

function printGestãoCabanas()
{
    echo "<div class='botaoCorpo'>
            <form action='../Cabanas/PgGestCabanas.php'>
                <input type='submit' value='Gestão Cabanas' id='btCorpo'>
            </form>
          </div>";
}

function printDadosPessoais()
{
    echo "<div class='botaoCorpo'>
            <form action='DadosPessoais.php'>
                <input type='submit' value='Dados Pessoais' id='btCorpo'>
            </form>
          </div>";
}

function printGestãoQuotas()
{
    echo "<div class='botaoCorpo'>
            <form action='PgQuotas.php'>
                <input type='submit' value='Gestão Quotas' id='btCorpo'>
            </form>
          </div>";
}

function printGestãoReservas()
{
    echo "<div class='botaoCorpo'>
            <form action='../Reserva/PgGestReservas.php'>
                <input type='submit' value='Gestão Reservas' id='btCorpo'>
            </form>
          </div>";
}

function printGestãoUtilizadores()
{
    echo "<div class='botaoCorpo'>
            <form action='PgGestUtilizadores.php'>
                <input type='submit' value='Gestão Utilizadores' id='btCorpo'>
            </form>
          </div>";
}
