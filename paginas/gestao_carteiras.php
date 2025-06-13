<?php
// Inicia a sessao PHP
session_start();
include "../basedados/basedados.h";
include "utilizadores.php";
include "carteira_funcoes.php";

// Verificar permissões (funcionário ou admin) e se o utilizador está autenticado
if (
    !isset($_SESSION['utilizador']) ||
    ($_SESSION['utilizador']['tipo_utilizador'] != FUNCIONARIO &&
        $_SESSION['utilizador']['tipo_utilizador'] != ADMINISTRADOR)
) {
    die("Acesso reservado a funcionários e administradores.");
}

// Mensagem de feedback
$mensagem = "";
// Variável para indicar erro
$erro = false;
// Variáveis para armazenar o saldo atual do cliente
$saldo_atual_cliente = null;
// Variável para armazenar o ID do cliente selecionado
$id_cliente_selecionado = null;

// Listar clientes
$resultado_clientes = $conn->query("SELECT id_utilizador, nome_utilizador
                                     FROM utilizador
                                     WHERE tipo_utilizador = " . CLIENTE);

// Verifica o metodo do form 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se o formulário de transação foi submetido
    if (isset($_POST['cliente'], $_POST['valor'], $_POST['tipo_movimento'])) {
        // Obter os dados do formulário de transação
        $id_cliente_selecionado = intval($_POST['cliente']);
        // Atribuir os valores do formulário
        $valor = $_POST['valor'];
        // Atribuir o tipo de movimento (adicionar ou retirar)
        $tipo_movimento = $_POST['tipo_movimento'];

        // Obter id_carteira do cliente
        // Preparar a consulta para obter o id_carteira do cliente selecionado
        $stmt = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
        // Faz o bind dos parâmetros
        $stmt->bind_param("i", $id_cliente_selecionado);
        // Executa a consulta
        $stmt->execute();
        // Obtém o resultado da consulta
        $res = $stmt->get_result();

        // Verifica se o cliente existe
        if ($res->num_rows === 0) {
            // Se não encontrar o cliente, define erro e mensagem
            $erro = true;
            $mensagem = "Cliente não encontrado.";
        } else {
            // Se encontrar o cliente, obtém o id_carteira
            $id_carteira = $res->fetch_assoc()["id_carteira"];

            // Verifica se o valor é válido
            if ($tipo_movimento === "adicionar") {
                // Adiciona o saldo
                $resultado = adicionarSaldo($conn, $id_carteira, $valor, ADICIONAR_SALDO);
            } elseif ($tipo_movimento === "retirar") {
                // Retira o saldo
                $resultado = retirarSaldo($conn, $id_carteira, $valor, RETIRAR_SALDO);
            } else {
                // Se o tipo de movimento não for válido, define erro e mensagem
                $resultado = ['success' => false, 'message' => "Tipo de movimento inválido."];
            }
            // Verifica o resultado da operação
            $erro = !$resultado['success'];
            // Define a mensagem de feedback
            $mensagem = $resultado['message'];
        }
    } elseif (isset($_POST['selecionar_cliente'])) {
        // Se o formulário de seleção de cliente foi submetido, obtém o ID do cliente selecionado
        $id_cliente_selecionado = intval($_POST['selecionar_cliente']);
    }
}

// Se um cliente foi selecionado, obter o saldo atual
if ($id_cliente_selecionado) {
    // Preparar a consulta para obter o id_carteira do cliente selecionado
    $stmt_saldo = $conn->prepare("SELECT id_carteira FROM utilizador WHERE id_utilizador = ?");
    // Verifica se a consulta foi preparada
    if ($stmt_saldo) {
        // Faz o bind dos parâmetros
        $stmt_saldo->bind_param("i", $id_cliente_selecionado);
        // Executa a consulta
        $stmt_saldo->execute();
        // Obtém o resultado da consulta
        $res_saldo = $stmt_saldo->get_result();

        // Verifica se o cliente tem uma carteira associada
        if ($res_saldo->num_rows > 0) {
            // Se encontrar o cliente, obtém o id_carteira
            $id_carteira_cliente = $res_saldo->fetch_assoc()["id_carteira"];
            // Obtém o saldo atual do cliente
            $saldo_atual_cliente = getSaldoAtual($conn, $id_carteira_cliente);
        }
        // Fecha a declaração
        $stmt_saldo->close();
    } else {
        // Se não conseguir preparar a consulta, define erro e mensagem
        $erro = true;
        // Define a mensagem de erro
        $mensagem = "Erro ao preparar a consulta para obter o ID da carteira: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Saldos</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body class="container mt-5">
    <h2>Gestão de Saldos de Clientes</h2>

    <!-- Exibir mensagem de feedback (se for erro danger, se não sucess) -->
    <?php if ($mensagem): ?>
        <div class="alert <?= $erro ? 'alert-danger' : 'alert-success' ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="selecionar_cliente" class="form-label">Cliente:</label>
            <select name="selecionar_cliente" id="selecionar_cliente" class="form-select" onchange="this.form.submit();" required>
                <option value="">-- Selecionar Cliente --</option>
                <?php
                // Verifica se há clientes disponíveis
                if ($resultado_clientes->num_rows > 0) {
                    // Se houver clientes, reinicia o ponteiro do resultado
                    $resultado_clientes->data_seek(0);
                }
                // Ciclo while que itera sobre os clientes e cria as opções do select
                while ($cli = $resultado_clientes->fetch_assoc()): ?>
                    <option value="<?= $cli['id_utilizador'] ?>" <?= ($id_cliente_selecionado == $cli['id_utilizador']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cli['nome_utilizador']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>

    <!-- Exibe o saldo atual do cliente selecionado, se houver -->
    <?php if ($saldo_atual_cliente !== null): ?>
        <div class="mb-3">
            <!-- Exibe o saldo atual do cliente selecionado -->
            <p>Saldo Atual do Cliente Selecionado: <strong><?= number_format($saldo_atual_cliente, 2, ',', '.') ?> €</strong></p>
        </div>
    <?php endif; ?>

    <!-- Formulário para adicionar ou retirar saldo -->
    <?php if ($id_cliente_selecionado): ?>
        <form method="POST" class="mt-4">
            <input type="hidden" name="cliente" value="<?= $id_cliente_selecionado ?>">

            <div class="mb-3">
                <label for="valor" class="form-label">Valor (€):</label>
                <input type="number" name="valor" step="0.01" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de movimento:</label><br>
                <input type="radio" name="tipo_movimento" value="adicionar" required> Adicionar saldo
                <input type="radio" name="tipo_movimento" value="retirar" required class="ms-3"> Retirar saldo
            </div>

            <button type="submit" class="btn btn-primary">Confirmar</button>
        </form>
    <?php else: ?>
        <!-- Mensagem para selecionar um cliente -->
        <p class="mt-4 alert alert-info">Por favor, selecione um cliente para gerir o saldo.</p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 30px;">
        <a href="pagina_utilizador.php">Voltar</a>
    </div>
</body>

</html>