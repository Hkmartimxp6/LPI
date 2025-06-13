<?php
include "../basedados/basedados.h";
include "utilizadores.php";
include "carteira_funcoes.php";

// Inicia a sessão PHP
session_start();

// Redireciona se o utilizador não estiver logado
if (!isset($_SESSION['utilizador'])) {
    // Redireciona para a página inicial
    header("Location: index.php");
    // sai do script
    exit();
}

// Associar o nome do utilizador que está na sessão
$nome_utilizador = $_SESSION['utilizador']['nome_utilizador'] ?? 'utilizador';
// O saldo atual do utilizador, que é zero por defeito
$saldo_atual = 0;
// Mensagem de feedback para o utilizador
$mensagem = "";
// Tipo de mensagem (success ou danger)
$tipo_mensagem = "";

// Verifica se as constantes das operações estão definidas
if (!defined('ADICIONAR_SALDO') || !defined('RETIRAR_SALDO')) {
    $mensagem = "Erro de configuração: Não foi possível encontrar os IDs das
                   operações 'Adicionar Saldo' ou 'Retirar Saldo' na configuração.
                   Por favor, verifique as constantes definidas.";
    $tipo_mensagem = "danger";
}

// Lipa as mensagens de sessão, se existirem
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo_mensagem = $_SESSION['tipo_mensagem'];
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}

// Lógica para processar depósitos e levantamentos (quando o formulário é submetido)
// Verifica se o método do form é POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter o ID da carteira do utilizador
    $id_carteira_utilizador = getIdCarteiraUtilizador($conn, $nome_utilizador);

    // Se não encontrar o ID da carteira do utilizador, mostra uma mensagem de erro
    if ($id_carteira_utilizador === null) {
        // Define a mensagem de erro
        $_SESSION['mensagem'] = "Erro: Não foi possível encontrar a carteira do utilizador para processar a transação.";
        // Com o tipo'danger' (vermelho)
        $_SESSION['tipo_mensagem'] = "danger";
        // Redireciona para a página da carteira
        header("Location: carteira.php");
        exit();
    } else {
        // Processar Depósito
        // Verifica se o botão de depósito foi pressionado
        if (isset($_POST['depositar'])) {
            // Obtém o valor do depósito do formulário
            $valor_deposito = filter_input(INPUT_POST, 'valor_deposito', FILTER_VALIDATE_FLOAT);

            // Verifica se o valor do depósito é válido
            if ($valor_deposito === false || $valor_deposito <= 0) {
                // Define a mensagem de erro
                $_SESSION['mensagem'] = "Valor de depósito inválido. Por favor, insira um valor positivo.";
                // Com o tipo 'danger' (vermelho)
                $_SESSION['tipo_mensagem'] = "danger";
            } else {
                // Chama a função para adicionar saldo
                $resultado = adicionarSaldo($conn, $id_carteira_utilizador, $valor_deposito, ADICIONAR_SALDO);
                // Guarda a mensagem na sessão
                $_SESSION['mensagem'] = $resultado['message'];
                // Define o tipo de mensagem com base no sucesso da operação
                $_SESSION['tipo_mensagem'] = $resultado['success'] ? 'success' : 'danger';
            }
            // Redireciona para a página da carteira
            header("Location: carteira.php");
            exit();
        }

        // Processar Levantamento
        // Verifica se o botão de levantamento foi pressionado
        elseif (isset($_POST['levantar'])) {
            // Obtém o valor do levantamento do formulário com validação do tipo float
            $valor_levantamento = filter_input(INPUT_POST, 'valor_levantamento', FILTER_VALIDATE_FLOAT);

            // Verifica se o valor do levantamento é válido
            if ($valor_levantamento === false || $valor_levantamento <= 0) {
                // Define a mensagem de erro
                $_SESSION['mensagem'] = "Valor de levantamento inválido. Por favor, insira um valor positivo.";
                // Com o tipo 'danger' (vermelho)
                $_SESSION['tipo_mensagem'] = "danger";
            } else {
                // Chama a função para retirar saldo
                $resultado = retirarSaldo($conn, $id_carteira_utilizador, $valor_levantamento, RETIRAR_SALDO);
                // Guarda a mensagem na sessão
                $_SESSION['mensagem'] = $resultado['message'];
                // Define o tipo de mensagem com base no sucesso da operação
                $_SESSION['tipo_mensagem'] = $resultado['success'] ? 'success' : 'danger';
            }
            // Redireciona para a página da carteira
            header("Location: carteira.php");
            exit();
        }
    }
}

// Lógica para obter o saldo atual do utilizador para exibição
// Obter o ID da carteira do utilizador
$id_carteira_utilizador = getIdCarteiraUtilizador($conn, $nome_utilizador);

// Se o ID da carteira do utilizador for encontrado
if ($id_carteira_utilizador !== null) {
    // Obtém o saldo atual
    $saldo_atual = getSaldoAtual($conn, $id_carteira_utilizador);
} else {
    // Se não encontrar o ID da carteira do utilizador, define uma mensagem de erro
    if (empty($mensagem)) {
        // Define a mensagem de erro
        $mensagem = "Erro: ID da carteira do utilizador não encontrado para '$nome_utilizador'.";
        // Define o tipo de mensagem como 'danger' (vermelho)
        $tipo_mensagem = "danger";
    }
}

// Fechar a conexão com a base de dados
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carteira - Felix Bus</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
</head>

<body class="main-layout">
    <div class="wallet-container">
        <div class="container">
            <div class="wallet-card">
                <div class="wallet-header">
                    <h2>Minha Carteira</h2>
                    <p>Saldo Atual:</p>
                    <!-- mostra o saldo do utilizador através da var $saldo_atual -->
                    <div class="balance-display"><?php echo number_format($saldo_atual, 2, ',', '.'); ?>€</div>
                </div>
                <!-- Mostra a mensagem com o tipo (sucesso? se sim usar o alert sucess, se não o alert-danger) -->
                <?php if (!empty($mensagem)): ?>
                    <div class="alerts">
                        <div class="alert <?php echo $tipo_mensagem == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo $mensagem; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="wallet-body">
                    <div id="separador_deposito" class="conteudo_transacao">
                        <h3 class="titulo_secao">Depositar Dinheiro</h3>
                        <form method="post" action="carteira.php">
                            <div class="form-group">
                                <label for="valor_deposito">Valor do Depósito (€)</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="valor_deposito" name="valor_deposito" required>
                            </div>
                            <button type="submit" name="depositar" class="btn-action">Confirmar Depósito</button>
                        </form>
                    </div>

                    <div id="separador_levantamento" class="conteudo_transacao" style="margin-top: 30px;">
                        <h3 class="titulo_secao">Levantar Dinheiro</h3>
                        <form method="post" action="carteira.php">
                            <div class="form-group">
                                <label for="valor_levantamento">Valor do Levantamento (€)</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="valor_levantamento" name="valor_levantamento" required>
                            </div>
                            <button type="submit" name="levantar" class="btn-action btn-withdraw">Confirmar Levantamento</button>
                        </form>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="index.php">Voltar para o Início</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>