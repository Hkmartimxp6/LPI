<?php
include "../basedados/basedados.h";
include "utilizadores.php";
include "carteira_funcoes.php";

// Inicia a sessão PHP
session_start();

// Redireciona se o utilizador não estiver logado
if (!isset($_SESSION['utilizador'])) {
    header("Location: login.php");
    exit();
}

$nome_utilizador = $_SESSION['utilizador']['nome_utilizador'] ?? 'utilizador';
$saldo_atual = 0;
$mensagem = "";
$tipo_mensagem = "";

// Verifica se as constantes das operações estão definidas
if (!defined('ADICIONAR_SALDO') || !defined('RETIRAR_SALDO')) {
    $mensagem = "Erro de configuração: Não foi possível encontrar os IDs das
                   operações 'Adicionar Saldo' ou 'Retirar Saldo' na configuração.
                   Por favor, verifique as constantes definidas.";
    $tipo_mensagem = "danger";
}

// Retrieve message and type from session after redirect
if (isset($_SESSION['msg'])) {
    $mensagem = $_SESSION['msg'];
    $tipo_mensagem = $_SESSION['type'];
    unset($_SESSION['msg']);
    unset($_SESSION['type']);
    unset($_SESSION['separador_ativo']); // Ainda é bom limpar, caso tenha sido definido antes
}

// Lógica para processar depósitos e levantamentos (quando o formulário é submetido)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_carteira_utilizador = getIdCarteiraUtilizador($conn, $nome_utilizador);

    if ($id_carteira_utilizador === null) {
        $_SESSION['msg'] = "Erro: Não foi possível encontrar a carteira do utilizador para processar a transação.";
        $_SESSION['type'] = "danger";
        header("Location: carteira.php");
        exit();
    } else {
        // Processar Depósito
        if (isset($_POST['depositar'])) {
            $valor_deposito = filter_input(INPUT_POST, 'valor_deposito', FILTER_VALIDATE_FLOAT);

            if ($valor_deposito === false || $valor_deposito <= 0) {
                $_SESSION['msg'] = "Valor de depósito inválido. Por favor, insira um valor positivo.";
                $_SESSION['type'] = "danger";
            } else {
                $resultado = adicionarSaldo($conn, $id_carteira_utilizador, $valor_deposito, ADICIONAR_SALDO);
                $_SESSION['msg'] = $resultado['message'];
                $_SESSION['type'] = $resultado['success'] ? 'success' : 'danger';
            }
            header("Location: carteira.php");
            exit();
        }
        // Processar Levantamento
        elseif (isset($_POST['levantar'])) {
            $valor_levantamento = filter_input(INPUT_POST, 'valor_levantamento', FILTER_VALIDATE_FLOAT);

            if ($valor_levantamento === false || $valor_levantamento <= 0) {
                $_SESSION['msg'] = "Valor de levantamento inválido. Por favor, insira um valor positivo.";
                $_SESSION['type'] = "danger";
            } else {
                $resultado = retirarSaldo($conn, $id_carteira_utilizador, $valor_levantamento, RETIRAR_SALDO);
                $_SESSION['msg'] = $resultado['message'];
                $_SESSION['type'] = $resultado['success'] ? 'success' : 'danger';
            }
            header("Location: carteira.php");
            exit();
        }
    }
}

// Lógica para obter o saldo atual do utilizador para exibição
$id_carteira_utilizador = getIdCarteiraUtilizador($conn, $nome_utilizador);

if ($id_carteira_utilizador !== null) {
    $saldo_atual = getSaldoAtual($conn, $id_carteira_utilizador);
} else {
    if (empty($mensagem)) { // Only set error if no other message is pending
        $mensagem = "Erro: ID da carteira do utilizador não encontrado para '$nome_utilizador'.";
        $tipo_mensagem = "danger";
    }
}

// Fechar a conexão com a base de dados
$conn->close();

$utilizador_logado = true;
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
                    <div class="balance-display"><?php echo number_format($saldo_atual, 2, ',', '.'); ?>€</div>
                </div>

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