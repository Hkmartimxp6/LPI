<?php
// Inclui ficheiros de base de dados e funções de utilizadores
include "../basedados/basedados.h";
include "utilizadores.php";

// Inicializa a sessão PHP
session_start();

// Verifica se a sessão está ativa e válida
if (!isset($_SESSION["utilizador"]) || !isset($_SESSION["utilizador"]["nome_utilizador"])) {
    header("Location: logout.php");
    exit();
}

// Obtém o nome de utilizador da sessão
$nome_utilizador = $_SESSION["utilizador"]["nome_utilizador"];

// Consulta os dados atualizados do utilizador na base de dados
$sql = "SELECT * FROM utilizador WHERE nome_utilizador = ?";
// Prepara a consulta para evitar SQL Injection
$stmt = $conn->prepare($sql);
// Faz o bind do parâmetro
$stmt->bind_param("s", $nome_utilizador);
// Executa a consulta e obtém o resultado
$stmt->execute();
// Obtém o resultado da consulta
$result = $stmt->get_result();

// Se não encontrar o utilizador, faz logout
if (!$result || $result->num_rows == 0) {
    header("Location: logout.php");
    exit();
}

// Busca os dados do utilizador
$row = $result->fetch_assoc();

// Verifica se o utilizador está num estado inválido
if ($row["tipo_utilizador"] == 4 || $row["tipo_utilizador"] == 5) {
    header("Location: logout.php");
    exit();
}

// Início do HTML do cabeçalho
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

// Conteúdo principal baseado no tipo de utilizador
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

// Botão de logout
echo "
    <div id='botao'>
        <form action='logout.php'><input type='submit' value='Logout'></form>
    </div>";

// === Funções para imprimir botões ===

function printGestaoBilhetes()
{
    // Botão para gestão de bilhetes
    echo "<div class='botaoCorpo'>
            <form action='gestao_bilhetes.php'>
                <input type='submit' value='Gestão Bilhetes' id='btCorpo'>
            </form>
          </div>";
}

function printCompraBilhetesParaCliente()
{
    // Botão para compra de bilhetes para clientes
    echo "<div class='botaoCorpo'>
            <form action='comprar_bilhetes_funcionario.php'>
                <input type='submit' value='Compra Bilhetes para Clientes' id='btCorpo'>
            </form>
          </div>";
}

function printDadosPessoais()
{
    // Botão para dados pessoais
    echo "<div class='botaoCorpo'>
            <form action='pagina_dados_pessoais.php'>
                <input type='submit' value='Dados Pessoais' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoCarteiraPessoal()
{
    // Botão para gestão da carteira pessoal
    echo "<div class='botaoCorpo'>
            <form action='carteira.php'>
                <input type='submit' value='Gestão Carteira Pessoal' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoCarteiras()
{
    // Botão para gestão de carteiras dos clientes
    echo "<div class='botaoCorpo'>
            <form action='gestao_carteiras.php'>
                <input type='submit' value='Gestão de Carteiras dos Clientes' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoRotas()
{
    // Botão para gestão de rotas
    echo "<div class='botaoCorpo'>
            <form action='gestao_rotas.php'>
                <input type='submit' value='Gestão Rotas' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoUtilizadores()
{
    // Botão para gestão de utilizadores
    echo "<div class='botaoCorpo'>
            <form action='gestao_utilizadores.php'>
                <input type='submit' value='Gestão Utilizadores' id='btCorpo'>
            </form>
          </div>";
}

function printGestaoAlertas()
{
    // Botão para gestão de alertas
    echo "<div class='botaoCorpo'>
            <form action='gestao_alertas.php'>
                <input type='submit' value='Gestão Alertas' id='btCorpo'>
            </form>
          </div>";
}
